<?php

class CRM_CivirulesActions_Activity_PushingToDovecare extends CRM_Civirules_Action {
    private $db;
    private $config;

    public function __construct() {
        // Load the config file
        $this->config = include('config.php');
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

        // Fetch Contact Details
        $contactDetails = $this->fetchData('Contact', 'get', [
            'select' => ['first_name', 'birth_date'],
            'where' => [['id', '=', $contactId]],
            'limit' => 1
        ]);

        if ($contactDetails === false) {
            Civi::log()->debug("Failed to fetch contact details for ID: " . $contactId);
            return false;
        }

        Civi::log()->debug("Contact details: " . print_r($contactDetails, true));

        $username = $contactDetails['first_name'];
        $dateOfBirth = $contactDetails['birth_date'];
        $firstName = $contactDetails['first_name'];

        // Fetch Phone Number
        $phoneDetails = $this->fetchData('Phone', 'get', [
            'select' => ['phone'],
            'where' => [['contact_id', '=', $contactId]]
        ]);

        if ($phoneDetails === false) {
            Civi::log()->debug("Failed to fetch phone details for contact ID: " . $contactId);
            return false;
        }

        Civi::log()->debug("Phone details: " . print_r($phoneDetails, true));

        $phoneNumber = $phoneDetails['phone'];

        // Check if user exists
        if ($this->checkUserExists($phoneNumber)) {
            Civi::log()->debug("User already exists with phone number: " . $phoneNumber);
            return true;
        }

        // Insert New User
        $firstThreeLetters = substr($firstName, 0, 3);
        $firstThreeLetters = strtolower($firstThreeLetters);
        $dateOfBirthFormatted = str_replace('-', '', $dateOfBirth);
        $password = $firstThreeLetters . $dateOfBirthFormatted;
        $options = ['cost' => 10];
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);
        $hashedPassword = str_replace('$2y$', '$2b$', $hashedPassword);
        $language = 'English';
        $imageUrl = 0; 
        //$imageUrl = $this->generateRandomImageUrl();

        if (!$this->insertUser($username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl)) {
            Civi::log()->debug("Failed to insert user: " . $username);
            return false;
        }

        Civi::log()->debug("User inserted successfully: " . $username);

        // Get User ID
        $userId = $this->getUserId($contactId);
        if ($userId === false) {
            Civi::log()->debug("Failed to retrieve user ID for contact ID: " . $contactId);
            return false;
        }

        Civi::log()->debug("User ID retrieved: " . $userId);

        // Fetch Address
        $addressDetails = $this->fetchData('Address', 'get', [
            'select' => ['street_address', 'postal_code'],
            'where' => [['contact_id', '=', $contactId]],
            'limit' => 1
        ]);

        if ($addressDetails === false) {
            Civi::log()->debug("Failed to fetch address details for contact ID: " . $contactId);
            return false;
        }

        Civi::log()->debug("Address details: " . print_r($addressDetails, true));

        $addressLine = $addressDetails['street_address'];
        $unitNo = '#' . $contactDetails['Additional_Senior_Details.Unit'];
        $postalCode = $addressDetails['postal_code'];

        // Insert Address
        if (!$this->insertAddress($userId, $addressLine, $unitNo, $postalCode)) {
            Civi::log()->debug("Failed to insert address for user ID: " . $userId);
            return false;
        }

        Civi::log()->debug("Address inserted successfully for user ID: " . $userId);

        return true;
    }

    private function fetchData($entity, $action, $params) {
        $params['checkPermissions'] = TRUE;
        Civi::log()->debug("Fetching data for entity: $entity, action: $action, params: " . json_encode($params));
        $result = civicrm_api4($entity, $action, $params);
    
        // Log the full result for debugging
        Civi::log()->debug("API result structure: " . print_r($result, true));
    
        // Check for errors
        if (isset($result['is_error']) && $result['is_error']) {
            $errorMessage = isset($result['error_message']) ? $result['error_message'] : 'Unknown error';
            Civi::log()->debug("API error: " . $errorMessage);
            return false;
        }
    
        if (empty($result)) {
            Civi::log()->debug("No data returned for entity: $entity, action: $action");
            return false;
        }
    
        Civi::log()->debug("Data fetched successfully for entity: $entity, action: $action");
        return $result[0];
    }
    
    private function checkUserExists($phoneNumber) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE phoneNumber = ?");
        if ($checkStmt === false || !$checkStmt->bind_param('s', $phoneNumber) || !$checkStmt->execute()) {
            Civi::log()->debug("Failed to prepare or execute user existence check for phone number: " . $phoneNumber);
            return false;
        }

        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        Civi::log()->debug("User existence check completed for phone number: " . $phoneNumber . " - Count: " . $count);
        return $count > 0;
    }

    private function insertUser($username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl) {
        $stmt = $this->db->prepare("INSERT INTO Users (username, phoneNumber, contactId, password, dateOfBirth, language, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false || !$stmt->bind_param('sssssss', $username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl) || !$stmt->execute()) {
            Civi::log()->debug("Failed to prepare or execute user insertion: " . $username);
            return false;
        }

        $stmt->close();
        Civi::log()->debug("User inserted successfully: " . $username);
        return true;
    }

    private function getUserId($contactId) {
        $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
        if ($query === false || !$query->bind_param('i', $contactId) || !$query->execute() || !$query->bind_result($userId) || !$query->fetch()) {
            Civi::log()->debug("Failed to prepare, execute, or fetch user ID for contact ID: " . $contactId);
            return false;
        }

        $query->close();
        Civi::log()->debug("User ID fetched successfully for contact ID: " . $contactId . " - User ID: " . $userId);
        return $userId;
    }

    private function insertAddress($userId, $addressLine, $unitNo, $postalCode) {
        $stmt = $this->db->prepare("INSERT INTO Addresses (userId, addressLine, unitNo, postalCode) VALUES (?, ?, ?, ?)");
        if ($stmt === false || !$stmt->bind_param('isss', $userId, $addressLine, $unitNo, $postalCode) || !$stmt->execute()) {
            Civi::log()->debug("Failed to prepare or execute address insertion for user ID: " . $userId);
            return false;
        }

        $stmt->close();
        Civi::log()->debug("Address inserted successfully for user ID: " . $userId);
        return true;
    }

    public function __destruct() {
        if ($this->db) {
            $this->db->close();
        }
    }

    public function getExtraDataInputUrl($ruleActionId) {
        return false;
    }

    public function getLabel() {
        return ts('Pushing to Dovecare');
    }

    public function getDescription() {
        return ts('Push data to Dovecare');
    }
}
?>
