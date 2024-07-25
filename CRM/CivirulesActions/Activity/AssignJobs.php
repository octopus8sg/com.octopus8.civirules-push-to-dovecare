<?php

class CRM_CivirulesActions_Activity_AssignJobs extends CRM_Civirules_Action {
    private $db;
    private $config;

    public function __construct() {
        // Load the config file
        $this->config = include(__DIR__ . '/../../config.php');
        Civi::log()->debug("Config loaded: " . print_r($this->config, true));

        // Connect to the custom database
        $this->db = new mysqli(
            $this->config['database']['host'], 
            $this->config['database']['username'], 
            $this->config['database']['password'], 
            $this->config['database']['dbname']
        );

        if ($this->db->connect_error) {
            Civi::log()->debug("Connection failed: " . $this->db->connect_error);
            die("Connection failed: " . $this->db->connect_error);
        } else {
            Civi::log()->debug("Database connection successful.");
        }
    }

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        $contactId = $triggerData->getContactId();
        Civi::log()->debug("Processing action for contact ID: " . $contactId);

        $this->fetchAndUpdateJobs($contactId);
    }

    public function getExtraDataInputUrl($ruleActionId) {
        return false;
    }

    public function fetchAndUpdateJobs($contactId) {
        // Fetch job data from the API
        $activities = civicrm_api4('Activity', 'get', [
            'select' => [
                'Updated_Job_Creation.title',
                'Updated_Job_Creation.Address',
                'activity_date_time',
                'id',
            ],
            'where' => [
                ['activity_type_id', '=', 70],
                ['assignee_contact_id', 'IS NOT EMPTY'],
            ],
            'orderBy' => [
                'modified_date' => 'DESC',
            ],
            'limit' => 1,
            'checkPermissions' => TRUE,
        ]);

        Civi::log()->debug("API resultssss: " . json_encode($activities));

        if (isset($activities['is_error']) && $activities['is_error']) {
            $errorMessage = isset($activities['error_message']) ? $activities['error_message'] : 'Unknown error';
            Civi::log()->debug("API error: " . $errorMessage);
            return false;
        }

        if (empty($activities)) {
            Civi::log()->debug("No activities returned");
            return false;
        }

        foreach ($activities as $activity) {
            $title = $activity['Updated_Job_Creation.title'];
            $address = $activity['Updated_Job_Creation.Address'];
            $activityDateTime = $activity['activity_date_time'];
            $activityId = $activity['id'];

            // Check if job already exists
            $jobId = $this->getJobId($title, $address);
            if ($jobId) {
                Civi::log()->debug("Job already exists with title: " . $title . ", address: " . $address);
            } else {
                // Insert new job if it doesn't exist
                $jobId = $this->insertJob($title, $address);
                if (!$jobId) {
                    Civi::log()->debug("Failed to insert job: " . $title);
                    return false;
                }
                Civi::log()->debug("Job inserted successfully: " . $title);
            }

            // Insert into JobDateTime
            $jobDateTimeId = $this->insertJobDateTime($jobId, $activityDateTime);
            if (!$jobDateTimeId) {
                Civi::log()->debug("Failed to insert job datetime for job ID: " . $jobId);
                return false;
            }
            Civi::log()->debug("Job datetime inserted successfully for job ID: " . $jobId);

            // Fetch userId from Users table
            $userId = $this->getUserId($contactId);
            if (!$userId) {
                Civi::log()->debug("Failed to fetch userId for contactId: " . $contactId);
                return false;
            }

            // Insert into JobSignups
            if (!$this->insertJobSignup($userId, $jobId, $jobDateTimeId, $activityId)) {
                Civi::log()->debug("Failed to insert job signup for user ID: " . $userId . ", job ID: " . $jobId . ", job datetime ID: " . $jobDateTimeId);
                return false;
            }
            Civi::log()->debug("Job signup inserted successfully for user ID: " . $userId . ", job ID: " . $jobId . ", job datetime ID: " . $jobDateTimeId);
            
            if (!$this->updateActivityStatus($activityId)) {
                Civi::log()->debug("Failed to update activity status for activity ID: " . $activityId);
                return false;
            }
            Civi::log()->debug("Activity status updated successfully for activity ID: " . $activityId);
        }

        return true;
    }

    private function getJobId($title, $address) {
        $stmt = $this->db->prepare("SELECT id FROM Jobs WHERE title = ? AND address = ?");
        if ($stmt === false) {
            Civi::log()->debug("Failed to prepare job existence check: " . $this->db->error);
            return false;
        }

        if (!$stmt->bind_param('ss', $title, $address)) {
            Civi::log()->debug("Failed to bind parameters for job existence check: " . $stmt->error);
            return false;
        }

        if (!$stmt->execute()) {
            Civi::log()->debug("Failed to execute job existence check: " . $stmt->error);
            return false;
        }

        $stmt->bind_result($jobId);
        if ($stmt->fetch()) {
            $stmt->close();
            return $jobId;
        }

        $stmt->close();
        return false;
    }

    private function insertJob($title, $address) {
        $stmt = $this->db->prepare("INSERT INTO Jobs (userId, title, description, address, radius, status) VALUES (?, ?, ?, ?, ?, ?)");
        $userId = 1; // Example user ID
        $description = 'Example description'; // Example description
        $radius = '5km'; // Example radius
        $status = 'pending'; // Example status

        if ($stmt === false) {
            Civi::log()->debug("Failed to prepare job insertion: " . $this->db->error);
            return false;
        }

        if (!$stmt->bind_param('isssss', $userId, $title, $description, $address, $radius, $status)) {
            Civi::log()->debug("Failed to bind parameters for job insertion: " . $stmt->error);
            return false;
        }

        if (!$stmt->execute()) {
            Civi::log()->debug("Failed to execute job insertion: " . $stmt->error);
            return false;
        }

        $jobId = $stmt->insert_id; // Get the ID of the inserted job
        $stmt->close();
        return $jobId;
    }

    private function insertJobDateTime($jobId, $activityDateTime) {
        $dateTime = new DateTime($activityDateTime);
        $date = $dateTime->format('Y-m-d');
        $time = $dateTime->format('H:i:s');

        $stmt = $this->db->prepare("INSERT INTO JobDateTimes (jobId, date, time, maxParticipants) VALUES (?, ?, ?, ?)");
        $maxParticipants = 10; // Example max participants

        if ($stmt === false || !$stmt->bind_param('issi', $jobId, $date, $time, $maxParticipants) || !$stmt->execute()) {
            Civi::log()->debug("Failed to prepare or execute job datetime insertion for job ID: " . $jobId . " Error: " . $this->db->error);
            return false;
        }

        $jobDateTimeId = $stmt->insert_id; // Get the ID of the inserted job datetime
        $stmt->close();
        return $jobDateTimeId;
    }

    private function getUserId($contactId) {
        Civi::log()->debug("Fetching user ID for contactId: " . $contactId);
        $stmt = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
        if ($stmt === false) {
            Civi::log()->debug("Failed to prepare user ID fetch: " . $this->db->error);
            return false;
        }

        if (!$stmt->bind_param('i', $contactId)) {
            Civi::log()->debug("Failed to bind parameters for user ID fetch: " . $stmt->error);
            return false;
        }

        if (!$stmt->execute()) {
            Civi::log()->debug("Failed to execute user ID fetch: " . $stmt->error);
            return false;
        }

        $stmt->bind_result($userId);
        if ($stmt->fetch()) {
            $stmt->close();
            return $userId;
        }

        $stmt->close();
        return false;
    }

    private function insertJobSignup($userId, $jobId, $jobDateTimeId, $activityId) {
        $stmt = $this->db->prepare("INSERT INTO JobSignups (userId, jobId, jobDateTimeId, activityID, status) VALUES (?, ?, ?, ?, ?)");
        $status = 'Pending'; // Example status

        if ($stmt === false) {
            Civi::log()->debug("Failed to prepare job signup insertion: " . $this->db->error);
            return false;
        }

        if (!$stmt->bind_param('iiiss', $userId, $jobId, $jobDateTimeId, $activityId, $status)) {
            Civi::log()->debug("Failed to bind parameters for job signup insertion: " . $stmt->error);
            return false;
        }

        if (!$stmt->execute()) {
            Civi::log()->debug("Failed to execute job signup insertion: " . $stmt->error);
            return false;
        }

        $stmt->close();
        return true;
    }
    private function updateActivityStatus($activityId) {
        $results = civicrm_api4('Activity', 'update', [
            'values' => [
                'status_id' => 10,
            ],
            'where' => [
                ['id', '=', $activityId],
            ],
            'checkPermissions' => TRUE,
        ]);

        if (isset($results['is_error']) && $results['is_error']) {
            $errorMessage = isset($results['error_message']) ? $results['error_message'] : 'Unknown error';
            Civi::log()->debug("Failed to update activity status: " . $errorMessage);
            return false;
        }

        return true;
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }

    public function getLabel() {
        return ts('Assign Jobs');
    }

    public function getDescription() {
        return ts('Fetches job data from API and updates Dovecare database.');
    }
}