<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class CRM_CivirulesActions_Activity_ApproveEventInDovecare extends CRM_Civirules_Action {

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
            die("Connection failed: " . $this->db->connect_error);
        }

        // API configuration
        $apiConfig = $config['api'];
        $this->apiUrl = $apiConfig['url'];
        $this->apiKey = $apiConfig['key'];

        // Log the initialization
        Civi::log()->debug("Database and API initialized: DB Host - {$dbConfig['host']}, API URL - {$this->apiUrl}");
    }

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        // For testing, using a hardcoded contact ID
        $contactId = 250;

        Civi::log()->debug("Processing action for Contact ID: $contactId");

        $url = $this->apiUrl . '/Activity/get';
        $params = [
            'select' => [
                'id',
                'subject', 
                'Event_Field.address', 
                'details', 
                'activity_date_time', 
                'Event_Field.Maximum_number_of_participants', 
                'Event_Field.Event_Domain:label',
                'Event_Field.Event_Type',
                'Event_Field.Image',
                'Event_Field.Duration_mins_',
                'Event_Field.Group_AAP_is_catered_to',
            ],
            'where' => [
                ['activity_type_id', '=', 67], 
                ['status_id', '=', 12], 
                //['assignee_contact_id', '=', $contactId], 
                ['Event_Field.mode_of_participation', '!=', 2]
            ],
            'orderBy' => ['modified_date' => 'DESC'],
            'limit' => 10,
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
            Civi::log()->error("Failed to fetch activities from API.");
            return false;
        }

        $activities = json_decode($activitiesResponse, TRUE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Civi::log()->error("JSON decode error: " . json_last_error_msg());
            return false;
        }

        if (empty($activities['values'])) {
            Civi::log()->debug("No recent activities found for the criteria.");
            return false;
        }

        // Get the most recent activity
        $recentActivity = $activities['values'][0];
        Civi::log()->debug("Most recent activity: " . json_encode($recentActivity));

        $title = $recentActivity['subject'];
        $address = $recentActivity['Event_Field.address'];
        $description = strip_tags($recentActivity['details']); // Remove all <p> tags
        $domain = $recentActivity['Event_Field.Event_Domain:label'];
        $groupId = $recentActivity['Event_Field.Event_Type'];
        $activityID = $recentActivity['id'];
        $userId = 1;
        $imageUrl = 0; // Assuming generateRandomImageUrl() is defined in this class
        $status = 'approved';
        $createdAt = $updatedAt = date('Y-m-d H:i:s', strtotime('-8 hours'));
        $groupAAP = $recentActivity['Event_Field.Group_AAP_is_catered_to'];
        $CFSRange = $groupAAP == 1 ? 3 : 6;
        $eventRadius = '1km'; // Assuming no value for eventRadius

        //$fileId = $recentActivity['Event_Field.Image'];
		$fileId = 1;
        
        try {
            Civi::log()->debug("Attempting to retrieve file metadata for file ID: $fileId");

            // Retrieve the file metadata from CiviCRM
            $file = civicrm_api3('Attachment', 'getsingle', [
                'return' => ["url"],
                'id' => $fileId,
            ]);

            Civi::log()->debug("File metadata retrieved: " . json_encode($file));
            $mimeType = isset($file['mime_type']) ? $file['mime_type'] : 'application/octet-stream';

            // Check if the URL is provided in the API response
            $fileUrl = isset($file['url']) ? $file['url'] : '';
            Civi::log()->debug("URL extracted from API response: " . $fileUrl);

            Civi::log()->info("File URL from API: " . $fileUrl);

            // Initialize cURL session
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fileUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification for testing

            Civi::log()->debug("Attempting to fetch file content using cURL from URL: $fileUrl");

            // Execute cURL session
            $fileContent = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                $error_msg = curl_error($ch);
                Civi::log()->error("cURL error: " . $error_msg);
                throw new Exception("Failed to retrieve file content from URL: " . $fileUrl . " - cURL Error: " . $error_msg);
            } else {
                // Log the size of the file content
                $fileSize = strlen($fileContent);
                Civi::log()->info("File content retrieved successfully. Size: " . $fileSize . " bytes.");
            }

            // Close cURL session
            curl_close($ch);

            $bucketName = getenv('BUCKET_NAME');
            $keyName = 'uploads/' . basename($fileUrl); // Set the key (file path) in S3

            Civi::log()->debug("Initializing the S3 client: Bucket - $bucketName, Key - $keyName");

            // Initialize the S3 client
            $s3Client = new S3Client([
                'region' => getenv('BUCKET_REGION'),
                'version' => 'latest',
                'credentials' => [
                    'key' => getenv('ACCESS_KEY'),
                    'secret' => getenv('SECRET_ACCESS_KEY'),
                ],
            ]);

            Civi::log()->info("Attempting to upload file to S3: $bucketName/$keyName");

            try {
                // Upload the file to S3
                $result = $s3Client->putObject([
                    'Bucket' => $bucketName,
                    'Key' => $keyName,
                    'Body' => $fileContent,
                    'ContentType' => $mimeType, // Set the correct MIME type
                ]);

                $imageUrl = $result['ObjectURL']; // Store the S3 URL in $imageUrl
                Civi::log()->info("File uploaded successfully to S3. S3 URL: " . $imageUrl);

            } catch (AwsException $e) {
                // Output error message if fails
                Civi::log()->error("Failed to upload file to S3: " . $e->getMessage());
                throw new Exception("S3 Upload Error: " . $e->getMessage());
            }

            Civi::log()->info("Continuing after S3 upload...");

        } catch (Exception $e) {
            Civi::log()->error("Error: " . $e->getMessage());
        }

        // Check if the event already exists
        $existingEventId = $this->checkIfEventExists($activityID);
        if ($existingEventId !== null) {
            $eventId = $existingEventId;

            $updateStmt = $this->db->prepare("UPDATE Events SET groupId = ?, userId = ?, title = ?, imageUrl = ?, description = ?, eventAddress = ?, eventRadius = ?, status = ?, updatedAt = ?, CFSRange = ?, domain = ? WHERE id = ?");
            if ($updateStmt === false) {
                Civi::log()->error("Failed to prepare update statement for Events.");
                return false;
            }

            if (!$updateStmt->bind_param('iisssssssisi', $groupId, $userId, $title, $imageUrl, $description, $address, $eventRadius, $status, $updatedAt, $CFSRange, $domain, $eventId)) {
                Civi::log()->error("Failed to bind parameters for update statement.");
                $updateStmt->close();
                return false;
            }

            if (!$updateStmt->execute()) {
                Civi::log()->error("Failed to execute update statement for Events.");
                $updateStmt->close();
                return false;
            }

            $updateStmt->close();

        } else {
            // Insert the event into the Events table
            $stmt = $this->db->prepare("INSERT INTO Events (groupId, userId, title, imageUrl, description, eventAddress, eventRadius, status, createdAt, updatedAt, CFSRange, domain) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                Civi::log()->error("Failed to prepare insert statement for Events.");
                return false;
            }

            if (!$stmt->bind_param('iissssssssis', $groupId, $userId, $title, $imageUrl, $description, $address, $eventRadius, $status, $createdAt, $updatedAt, $CFSRange, $domain)) {
                Civi::log()->error("Failed to bind parameters for insert statement.");
                $stmt->close();
                return false;
            }

            if (!$stmt->execute()) {
                Civi::log()->error("Failed to execute insert statement for Events.");
                $stmt->close();
                return false;
            }

            $eventId = $stmt->insert_id; // Get the ID of the inserted event
            Civi::log()->info("New event inserted with ID: $eventId");
            $stmt->close();
        }

        // Insert all matching activities into the EventDateTimes table
        foreach ($activities['values'] as $activity) {
            if ($activity['subject'] == $title && $activity['Event_Field.address'] == $address && strip_tags($activity['details']) == $description) {
                $dateTime = $activity['activity_date_time'];
                $duration = $activity['Event_Field.Duration_mins_'];
                $maxParticipants = $activity['Event_Field.Maximum_number_of_participants'];
                $activityID = $activity['id']; // Add this line to capture the activity ID
                $date = date('Y-m-d', strtotime($dateTime));
                $time = date('H:i:s', strtotime($dateTime));
                $endTime = date('H:i:s', strtotime("+$duration minutes", strtotime($dateTime)));

                if ($this->checkIfActivityIDExists($activityID)) {
                    // Update the existing record
                    $updateStmt = $this->db->prepare("UPDATE EventDateTimes SET date = ?, time = ?, endTime = ?, maxParticipants = ?, updatedAt = ? WHERE activityID = ?");
                    if ($updateStmt === false) {
                        Civi::log()->error("Failed to prepare update statement for EventDateTimes.");
                        return false;
                    }

                    if (!$updateStmt->bind_param('sssisi', $date, $time, $endTime, $maxParticipants, $updatedAt, $activityID)) {
                        Civi::log()->error("Failed to bind parameters for update statement.");
                        $updateStmt->close();
                        return false;
                    }

                    if (!$updateStmt->execute()) {
                        Civi::log()->error("Failed to execute update statement for EventDateTimes.");
                        $updateStmt->close();
                        return false;
                    }

                    $updateStmt->close();

                } else {
                    // Insert a new record
                    $dateTimeStmt = $this->db->prepare("INSERT INTO EventDateTimes (eventId, date, time, endTime, activityID, maxParticipants, createdAt, updatedAt) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($dateTimeStmt === false) {
                        Civi::log()->error("Failed to prepare insert statement for EventDateTimes.");
                        return false;
                    }

                    if (!$dateTimeStmt->bind_param('isssiiss', $eventId, $date, $time, $endTime, $activityID, $maxParticipants, $createdAt, $updatedAt)) {
                        Civi::log()->error("Failed to bind parameters for insert statement.");
                        $dateTimeStmt->close();
                        return false;
                    }

                    if (!$dateTimeStmt->execute()) {
                        Civi::log()->error("Failed to execute insert statement for EventDateTimes.");
                        $dateTimeStmt->close();
                        return false;
                    }

                    $dateTimeStmt->close();
                }

                // Update the activity with remaining slots and new title
                $formattedDate = date('d/m/Y', strtotime($activity['activity_date_time']));
                $newTitle = $title . '@' . $formattedDate;
                $updateUrl = $this->apiUrl . '/Activity/update';
                $updateParams = [
                    'values' => [
                        'Event_Field.Remaining_participants_slot' => $activity['Event_Field.Maximum_number_of_participants'],
                        'Event_Field.title' => $newTitle
                    ],
                    'where' => [['id', '=', $activity['id']]],
                ];

                $updateRequest = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => [
                            'Content-Type: application/x-www-form-urlencoded',
                            'X-Civi-Auth: Bearer ' . $this->apiKey,
                        ],
                        'content' => http_build_query(['params' => json_encode($updateParams)]),
                    ],
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ]
                ]);

                $updateResponse = file_get_contents($updateUrl, FALSE, $updateRequest);
                if ($updateResponse === FALSE) {
                    Civi::log()->error("Failed to update activity in CiviCRM.");
                    return false;
                }

                $updateResults = json_decode($updateResponse, TRUE);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Civi::log()->error("JSON decode error during activity update: " . json_last_error_msg());
                    return false;
                }

                Civi::log()->info("Activity updated successfully with new title: $newTitle");
            }
        }

        return true; // Return true if the action was successful
    }

    private function checkIfActivityIDExists($activityID) {
        $stmt = $this->db->prepare("SELECT id FROM EventDateTimes WHERE activityID = ?");
        if ($stmt === false) {
            Civi::log()->error("Failed to prepare statement for checking activity ID in EventDateTimes.");
            return false;
        }

        if (!$stmt->bind_param('i', $activityID)) {
            Civi::log()->error("Failed to bind parameters for checking activity ID.");
            $stmt->close();
            return false;
        }

        if (!$stmt->execute()) {
            Civi::log()->error("Failed to execute statement for checking activity ID.");
            $stmt->close();
            return false;
        }

        $stmt->bind_result($dateTimeId);
        $stmt->fetch();
        $stmt->close();

        return $dateTimeId ? true : false;
    }

    private function checkIfEventExists($activityID) {
        // First, check if the activityID exists in the EventDateTimes table
        $stmt = $this->db->prepare("SELECT eventId FROM EventDateTimes WHERE activityID = ?");
        if ($stmt === false) {
            Civi::log()->error("Failed to prepare statement for checking event existence.");
            return null;
        }

        if (!$stmt->bind_param('i', $activityID)) {
            Civi::log()->error("Failed to bind parameters for checking event existence.");
            $stmt->close();
            return null;
        }

        if (!$stmt->execute()) {
            Civi::log()->error("Failed to execute statement for checking event existence.");
            $stmt->close();
            return null;
        }

        $stmt->bind_result($eventId);
        $stmt->fetch();
        $stmt->close();

        if (!$eventId) {
            return null;
        }

        // Now, check if the eventId exists in the Events table
        $stmt = $this->db->prepare("SELECT id FROM Events WHERE id = ?");
        if ($stmt === false) {
            Civi::log()->error("Failed to prepare statement for checking event ID in Events table.");
            return null;
        }

        if (!$stmt->bind_param('i', $eventId)) {
            Civi::log()->error("Failed to bind parameters for checking event ID in Events table.");
            $stmt->close();
            return null;
        }

        if (!$stmt->execute()) {
            Civi::log()->error("Failed to execute statement for checking event ID in Events table.");
            $stmt->close();
            return null;
        }

        $stmt->bind_result($existingEventId);
        $stmt->fetch();
        $stmt->close();

        return $existingEventId ? $existingEventId : null;
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