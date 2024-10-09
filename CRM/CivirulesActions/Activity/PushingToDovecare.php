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
        $contactDetails = $this->fetchData(
            $this->config['api']['url'] . '/Contact/get',
            [
                'select' => ['first_name', 'birth_date', 'Additional_Senior_Details.Unit',],
                'where' => [['id', '=', $contactId]],
                'limit' => 1
            ]
        );

        if ($contactDetails === false) {
            return false;
        }

        $username = $contactDetails['first_name'];
        $dateOfBirth = $contactDetails['birth_date'];
        $firstName = $contactDetails['first_name'];

        // Fetch Phone Number
        $phoneDetails = $this->fetchData(
            $this->config['api']['url'] . '/Phone/get',
            [
                'select' => ['phone'],
                'where' => [['contact_id', '=', $contactId]]
            ]
        );

        if ($phoneDetails === false) {
            return false;
        }

        $phoneNumber = $phoneDetails['phone'];

        // Check if user exists
        if ($this->checkUserExists($contactId)) {
            // Update User Details
            if (!$this->updateUser($username, $phoneNumber, $contactId, $dateOfBirth)) {
                return false;
            }
        } else {
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

            if (!$this->insertUser($username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl)) {
                return false;
            }
        }

        // Get User ID
        $userId = $this->getUserId($contactId);
        if ($userId === false) {
            return false;
        }

        // Fetch Address
        $addressDetails = $this->fetchData(
            $this->config['api']['url'] . '/Address/get',
            [
                'select' => ['street_address', 'postal_code'],
                'where' => [['contact_id', '=', $contactId]],
                'limit' => 1
            ]
        );

        if ($addressDetails === false) {
            return false;
        }

        $addressLine = $addressDetails['street_address'];
        $unitNo = '#' . $contactDetails['Additional_Senior_Details.Unit'];
        $postalCode = $addressDetails['postal_code'];

        // Insert or Update Address
        if (!$this->insertOrUpdateAddress($userId, $addressLine, $unitNo, $postalCode)) {
            return false;
        }

        // Insert Domain if new user, or do something else if updating;
        if (!$this->insertDomain($userId)) {
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

    private function insertUser($username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl) {
        $stmt = $this->db->prepare("INSERT INTO Users (username, phoneNumber, contactId, password, dateOfBirth, language, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            return false;
        }
        if (!$stmt->bind_param('sssssss', $username, $phoneNumber, $contactId, $hashedPassword, $dateOfBirth, $language, $imageUrl)) {
            return false;
        }
        if (!$stmt->execute()) {
            return false;
        }

        $stmt->close();
        return true;
    }

    private function updateUser($username, $phoneNumber, $contactId, $dateOfBirth) {
        $stmt = $this->db->prepare("UPDATE Users SET username = ?, phoneNumber = ?, dateOfBirth = ? WHERE contactId = ?");
        if ($stmt === false) {
            return false;
        }
        if (!$stmt->bind_param('sssi', $username, $phoneNumber, $dateOfBirth, $contactId)) {
            return false;
        }
        if (!$stmt->execute()) {
            return false;
        }

        $stmt->close();
        return true;
    }

    private function getUserId($contactId) {
        $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
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

    private function insertOrUpdateAddress($userId, $addressLine, $unitNo, $postalCode) {
        $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Addresses WHERE userId = ?");
        if ($checkStmt === false) {
            return false;
        }
        if (!$checkStmt->bind_param('i', $userId)) {
            return false;
        }
        if (!$checkStmt->execute()) {
            return false;
        }

        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            // Update existing address
            $stmt = $this->db->prepare("UPDATE Addresses SET addressLine = ?, unitNo = ?, postalCode = ? WHERE userId = ?");
            if ($stmt === false) {
                return false;
            }
            if (!$stmt->bind_param('sssi', $addressLine, $unitNo, $postalCode, $userId)) {
                return false;
            }
        } else {
            // Insert new address
            $stmt = $this->db->prepare("INSERT INTO Addresses (userId, addressLine, unitNo, postalCode) VALUES (?, ?, ?, ?)");
            if ($stmt === false) {

                return false;
            }
            if (!$stmt->bind_param('isss', $userId, $addressLine, $unitNo, $postalCode)) {
                return false;
            }
        }

        if (!$stmt->execute()) {

            return false;
        }

        $stmt->close();
        return true;
    }

    private function insertDomain($userId) {

        $stmt = $this->db->prepare("INSERT INTO Domains (userId, Physical, Cognitive, Social, CommunalDining, Learning, Volunteerism, createdAt, updatedAt) VALUES (?, 0, 0, 0, 0, 0, 0, NOW(), NOW())");

        // Log if the prepare statement fails
        if ($stmt === false) {
            return false;
        }

        // Log if the bind_param statement fails
        if (!$stmt->bind_param('i', $userId)) {
            return false;
        }

        // Log if the execute statement fails
        if (!$stmt->execute()) {
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