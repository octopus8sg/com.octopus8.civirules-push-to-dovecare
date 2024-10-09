<?php

class CRM_CivirulesActions_Activity_CreatingAAC extends CRM_Civirules_Action {
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
        //$contactId = $triggerData->getContactId();
        $contactId = 260;
        // Fetch Contact Details
        $contactDetails = $this->fetchData(
            $this->config['api']['url'] . '/Contact/get',
            [
                'select' => ['organization_name' ],
                'where' => [['id', '=', $contactId]],
                'orderBy' => ['id' => 'DESC'],
                'limit' => 1
            ]
        );

        if ($contactDetails === false) {
            return false;
        }

        $username = $contactDetails['organization_name'];

        // Fetch Email Number
        $emailDetails = $this->fetchData(
            $this->config['api']['url'] . '/Email/get',
            [
                'select' => ['email'],
                'where' => [['contact_id', '=', $contactId]],
                'orderBy' => ['id' => 'DESC'],
                'limit' => 1
            ]
        );

        if ($emailDetails === false) {
            return false;
        }

        $email = $emailDetails['email'];

        // Fetch Address
        $addressDetails = $this->fetchData(
            $this->config['api']['url'] . '/Address/get',
            [
                'select' => ['postal_code'],
                'where' => [['contact_id', '=', $contactId]],
                'orderBy' => ['id' => 'DESC'],
                'limit' => 1
            ]
        );

        if ($addressDetails === false) {
            return false;
        }

        $postalCode = $addressDetails['postal_code'];
        
        // Check if user exists
        if ($this->checkUserExists($contactId)) {
            // Update User Details
            if (!$this->updateUser($username, $postalCode, $email, $contactId)) {
                return false;
            }
        } else {
            // Insert New User
            $firstThreeLetters = substr($username, 0, 3);
            $firstThreeLetters = strtolower($firstThreeLetters);
            $password = $firstThreeLetters; //. $dateOfBirthFormatted;// change this
            $options = ['cost' => 10];
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, $options);
            $hashedPassword = str_replace('$2y$', '$2b$', $hashedPassword);
            $language = 'English';
            $imageUrl = 0; 
            $userType = 'Organization';

            if (!$this->insertUser($username, $postalCode, $contactId, $hashedPassword, $language, $imageUrl, $userType)) {
                return false;
            }
        }

        // Get User ID
        $userId = $this->getUserId($contactId);
        if ($userId === false) {
            return false;
        }

        return true;
    }

    private function fetchData($url, $params) {
        $request = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/x-www-form-urlencoded',
                    'X-Civi-Auth: Bearer ' . $this->config['api']['key']
                ],
                'content' => http_build_query(['params' => json_encode($params)])
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $response = file_get_contents($url, false, $request);
        if ($response === false) {
            return false;
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data['values'])) {
            return false;
        }

        return $data['values'][0];
    }

    private function checkUserExists($contactId) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE contactId = ?");
        if ($checkStmt === false || !$checkStmt->bind_param('i', $contactId) || !$checkStmt->execute()) {
            return false;
        }

        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        return $count > 0;
    }

    private function insertUser($username, $postalCode, $email, $contactId, $hashedPassword, $language, $imageUrl, $userType) {
        $stmt = $this->db->prepare("INSERT INTO Users (username, phoneNumber, emailAddress, contactId, password, language, imageUrl, userType) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            return false;
        }
        if (!$stmt->bind_param('ssssssss', $username, $postalCode, $email, $contactId, $hashedPassword, $language, $imageUrl, $userType)) {
            return false;
        }
        if (!$stmt->execute()) {
            return false;
        }

        $stmt->close();
        return true;
    }

    private function updateUser($username, $postalCode, $email, $contactId) {
        $stmt = $this->db->prepare("UPDATE Users SET username = ?, phoneNumber = ?, emailAddress = ? WHERE contactId = ?");
        if ($stmt === false) {
            return false;
        }
        if (!$stmt->bind_param('sssi', $username, $postalCode, $email, $contactId)) {
            return false;
        }
        if (!$stmt->execute()) {
            return false;
        }

        $stmt->close();
        return true;
    }

    private function getUserId($contactId) {
        $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ? AND userType = 'Organization'");
        if ($query === false) {
            return false;
        }
        if (!$query->bind_param('i', $contactId)) {
            return false;
        }
        if (!$query->execute()) {
            return false;
        }
        if (!$query->bind_result($userId)) {
            return false;
        }
        if (!$query->fetch()) {
            return false;
        }

        $query->close();
        return $userId;
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
        return ts('Pushing to Dovecare');//change this
    }

    public function getDescription() {
        return ts('Push data to Dovecare');
    }
}
?>