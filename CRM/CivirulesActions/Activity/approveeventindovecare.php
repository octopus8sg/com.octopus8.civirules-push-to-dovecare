<?php
class CRM_CivirulesActions_Activity_approveeventindovecare extends CRM_Civirules_Action {

        private $db;
        private $apiUrl;
        private $apiKey;
    
        public function __construct() {
            // Load configuration
            $config = include('config.php');
    
            // Database configuration
            $dbConfig = $config['database'];
            $this->db = new mysqli(
                $dbConfig['host'],
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['dbname']
            );
    
            if ($this->db->connect_error) {
                Civi::log()->error("Database connection failed", ['error' => $this->db->connect_error]);
                die("Connection failed: " . $this->db->connect_error);
            } else {
                Civi::log()->info("Database connected successfully");
            }
    
            // API configuration
            $apiConfig = $config['api'];
            $this->apiUrl = $apiConfig['url'];
            $this->apiKey = $apiConfig['key'];
        }
    
        public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
            $contactId = $triggerData->getContactId();
    
            $url = $this->apiUrl . '/Activity/get';
            $params = [
                'select' => [
                    'id',
                    'subject', 
                    'Event_Field.address', 
                    'details', 
                    'activity_date_time', 
                    'Event_Field.Maximum_number_of_participants', 
                    'Event_Field.domain',
                    'Event_Field.Event_Type',
                ],
                'where' => [
                    ['activity_type_id', '=', 67], 
                    ['status_id', '=', 12], 
                    ['source_contact_id', '=', $contactId], 
                    ['Event_Field.mode_of_participation', '!=', 2]
                ],
                'orderBy' => ['modified_date' => 'DESC'],
                'limit' => 1,
            ];
    
            $request = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-Type: application/x-www-form-urlencoded',
                        'X-Civi-Auth: Bearer ' . $this->apiKey,
                    ],
                    'content' => http_build_query(['params' => json_encode($params)]),
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
    
            $activitiesResponse = file_get_contents($url, FALSE, $request);
            if ($activitiesResponse === FALSE) {
                $error = error_get_last();
                Civi::log()->error("Failed to fetch activities data from API", ['error' => $error['message']]);
                return false;
            }
    
            // Log the raw response for debugging
            Civi::log()->info("API response received", ['response' => $activitiesResponse]);
    
            $activities = json_decode($activitiesResponse, TRUE);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Civi::log()->error("JSON decode error", ['error' => json_last_error_msg()]);
                return false;
            }
    
            if (empty($activities['values'])) {
                Civi::log()->error("No activity data found", ['contactId' => $contactId]);
                return false;
            }
    
            $activity = $activities['values'][0];
            $id =  $activity['id'];
            $title = $activity['subject'];
            $address = $activity['Event_Field.address'];
            $description = strip_tags($activity['details']); // Remove all <p> tags
            $dateTime = $activity['activity_date_time'];
            $maxParticipants = $activity['Event_Field.Maximum_number_of_participants'];
            $domain = $activity['Event_Field.domain'];
            $groupId = 1;
            $userId = 1;
            $imageUrl = 0; // Assuming generateRandomImageUrl() is defined in this class
            $status = 'approved';
            $createdAt = $updatedAt = date('Y-m-d H:i:s');
            $CFSRange = 1; // Assuming no value for CFSRange
            $eventRadius = '1km'; // Assuming no value for eventRadius
    
            // Check if the event already exists
            $existingEventId = $this->checkIfEventExists($title, $address, $description);
            if ($existingEventId !== null) {
                $eventId = $existingEventId;
                Civi::log()->info("Event already exists, using existing eventId: $eventId");
            } else {
                // Insert the event into the Events table
                $stmt = $this->db->prepare("INSERT INTO Events (groupId, userId, title, imageUrl, description, eventAddress, eventRadius, status, createdAt, updatedAt, CFSRange, domain) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    Civi::log()->error("Prepare failed", ['error' => $this->db->error]);
                    return false;
                }
    
                if (!$stmt->bind_param('iissssssssis', $groupId, $userId, $title, $imageUrl, $description, $address, $eventRadius, $status, $createdAt, $updatedAt, $CFSRange, $domain)) {
                    Civi::log()->error("Bind failed", ['error' => $stmt->error]);
                    $stmt->close();
                    return false;
                }
    
                if (!$stmt->execute()) {
                    Civi::log()->error("Execute failed", ['error' => $stmt->error]);
                    $stmt->close();
                    return false;
                }
    
                $eventId = $stmt->insert_id; // Get the ID of the inserted event
                $stmt->close();
    
                // Log the created event
                Civi::log()->info("Event record created successfully for title: $title, eventId: $eventId");
            }
    
            // Insert the event date and time into the EventDateTimes table
            $date = date('Y-m-d', strtotime($dateTime));
            $time = date('H:i:s', strtotime($dateTime));
    
            $dateTimeStmt = $this->db->prepare("INSERT INTO EventDateTimes (eventId, date, time, maxParticipants, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?)");
            if ($dateTimeStmt === false) {
                Civi::log()->error("Prepare failed", ['error' => $this->db->error]);
                return false;
            }
    
            if (!$dateTimeStmt->bind_param('ississ', $eventId, $date, $time, $maxParticipants, $createdAt, $updatedAt)) {
                Civi::log()->error("Bind failed", ['error' => $dateTimeStmt->error]);
                $dateTimeStmt->close();
                return false;
            }
    
            if (!$dateTimeStmt->execute()) {
                Civi::log()->error("Execute failed", ['error' => $dateTimeStmt->error]);
                $dateTimeStmt->close();
                return false;
            }
    
            Civi::log()->info("EventDateTimes record created successfully for eventId: $eventId");
    
            $dateTimeStmt->close();
            return true; // Return true if the action was successful
        }
    
        private function checkIfEventExists($title, $address, $description) {
            $stmt = $this->db->prepare("SELECT id FROM Events WHERE title = ? AND eventAddress = ? AND description = ?");
            if ($stmt === false) {
                Civi::log()->error("Prepare failed", ['error' => $this->db->error]);
                return null;
            }
    
            if (!$stmt->bind_param('sss', $title, $address, $description)) {
                Civi::log()->error("Bind failed", ['error' => $stmt->error]);
                $stmt->close();
                return null;
            }
    
            if (!$stmt->execute()) {
                Civi::log()->error("Execute failed", ['error' => $stmt->error]);
                $stmt->close();
                return null;
            }
    
            $stmt->bind_result($eventId);
            $stmt->fetch();
            $stmt->close();
    
            return $eventId ? $eventId : null;
        }
    
        public function __destruct() {
            // Close the database connection
            if ($this->db) {
                $this->db->close();
            }
        }
    
        public function getExtraDataInputUrl($ruleActionId) {
            return FALSE;
        }
    
        public function getLabel() {
            return ts('Pushing to Events');
        }
    
        public function getDescription() {
            return ts('Push activity data to Events');
        }
    }
    ?>