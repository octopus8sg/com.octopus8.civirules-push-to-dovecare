<?php

class CRM_CivirulesActions_Activity_PushingToDovecare extends CRM_Civirules_Action {
    private $db;
    private $config;

    public function __construct() {
        // Load the config file
        $this->config = include('config.php');

        // Connect to the custom database
        $this->db = new mysqli(
            $this->config['database']['host'], 
            $this->config['database']['username'], 
            $this->config['database']['password'], 
            $this->config['database']['dbname']
        );

        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        $contactId = $triggerData->getContactId();
        
        // Fetch Contact Details
        $contactDetails = $this->fetchData('Contact', 'get', [
            'select' => ['first_name', 'birth_date'],
            'where' => [['id', '=', $contactId]],
            'limit' => 1
        ]);

        if ($contactDetails === false) {
            return false;
        }

        $username = $contactDetails['first_name'];
        $dateOfBirth = $contactDetails['birth_date'];
        $firstName = $contactDetails['first_name'];

        // Fetch Phone Number
        $phoneDetails = $this->fetchData('Phone', 'get', [
            'select' => ['phone'],
            'where' => [['contact_id', '=', $contactId]]
        ]);

        if ($phoneDetails === false) {
            return false;
        }

        $phoneNumber = $phoneDetails['phone'];

        // Check if user exists
        if ($this->checkUserExists($phoneNumber)) {
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
            return false;
        }

        // Get User ID
        $userId = $this->getUserId($contactId);
        if ($userId === false) {
            return false;
        }

        // Fetch Address
        $addressDetails = $this->fetchData('Address', 'get', [
            'select' => ['street_address', 'postal_code'],
            'where' => [['contact_id', '=', $contactId]],
            'limit' => 1
        ]);

        if ($addressDetails === false) {
            return false;
        }

        $addressLine = $addressDetails['street_address'];
        $unitNo = '#' . $contactDetails['Additional_Senior_Details.Unit'];
        $postalCode = $addressDetails['postal_code'];

        // Insert Address
        if (!$this->insertAddress($userId, $addressLine, $unitNo, $postalCode)) {
            return false;
        }

        return true;
    }

    private function fetchData($entity, $action, $params) {
        $params['checkPermissions'] = TRUE;
        $result = civicrm_api4($entity, $action, $params);

        if (empty($result['values'])) {
            return false;
        }

        return $result['values'][0];
    }

    private function checkUserExists($phoneNumber) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE phoneNumber = ?");
        if ($checkStmt === false || !$checkStmt->bind_param('s', $phoneNumber) || !$checkStmt->execute()) {
            return false;
        }

        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        return $count > 0;
    }

    private function insertUser($username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl) {
        $stmt = $this->db->prepare("INSERT INTO Users (username, phoneNumber, contactId, password, dateOfBirth, language, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false || !$stmt->bind_param('sssssss', $username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl) || !$stmt->execute()) {
            return false;
        }

        $stmt->close();
        return true;
    }

    private function getUserId($contactId) {
        $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
        if ($query === false || !$query->bind_param('i', $contactId) || !$query->execute() || !$query->bind_result($userId) || !$query->fetch()) {
            return false;
        }

        $query->close();
        return $userId;
    }

    private function insertAddress($userId, $addressLine, $unitNo, $postalCode) {
        $stmt = $this->db->prepare("INSERT INTO Addresses (userId, addressLine, unitNo, postalCode) VALUES (?, ?, ?, ?)");
        if ($stmt === false || !$stmt->bind_param('isss', $userId, $addressLine, $unitNo, $postalCode) || !$stmt->execute()) {
            return false;
        }

        $stmt->close();
        return true;
    }

    private function generateRandomImageUrl() {
        $randomNumber = rand(1, 4);
        return "random_" . $randomNumber . ".jpg";
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
