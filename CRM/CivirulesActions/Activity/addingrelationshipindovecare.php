<?php

class CRM_CivirulesActions_Activity_AddingRelationshipinDovecare extends CRM_Civirules_Action {
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

    // Fetch the spouse's contact ID using the provided API request
    $url = $this->config['api']['url'] . '/Relationship/get';
    $params = [
      'select' => ['contact_id_b'],
      'where' => [['relationship_type_id', '=', 2], ['contact_id_a', '=', $contactId]],
      'orderBy' => ['id' => 'DESC'],
      'limit' => 1,
    ];
    $request = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => [
          'Content-Type: application/x-www-form-urlencoded',
          'X-Civi-Auth: Bearer ' . $this->config['api']['key'],
        ],
        'content' => http_build_query(['params' => json_encode($params)]),
      ],
      'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
      ]
    ]);

    $relationshipsResponse = file_get_contents($url, FALSE, $request);
    if ($relationshipsResponse === FALSE) {
        return false;
    }
    
    $relationships = json_decode($relationshipsResponse, TRUE);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return false;
    }

    if (empty($relationships['values'])) {
        return false;
    }

    $spouseContactId = $relationships['values'][0]['contact_id_b'];

    // Fetch the user IDs for the original contact and spouse
    $userId = $this->getUserIdByContactId($contactId);
    if ($userId === null) {
        return false;
    }

    $spouseUserId = $this->getUserIdByContactId($spouseContactId);
    if ($spouseUserId === null) {
        return false;
    }

    // Check and insert the relationship twice
    if (!$this->checkAndInsertRelationship($userId, $spouseUserId, $spouseContactId)) {
        return false;
    }

    if (!$this->checkAndInsertRelationship($spouseUserId, $userId, $contactId)) {
        return false;
    }

    return true;
  }

  private function getUserIdByContactId($contactId) {
    $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
    if ($query === false) {
        Civi::log()->error("Query preparation failed: " . $this->db->error);
        return null;
    }

    if (!$query->bind_param('i', $contactId)) {
        Civi::log()->error("Binding parameters failed: " . $query->error);
        $query->close();
        return null;
    }

    if (!$query->execute()) {
        Civi::log()->error("Query execution failed: " . $query->error);
        $query->close();
        return null;
    }

    $query->bind_result($userId);
    $query->fetch();
    $query->close();

    return $userId ? $userId : null;
  }

  private function checkAndInsertRelationship($userId, $spouseUserId, $spouseContactId) {
    // Check if the relationship already exists in the Spouses table
    $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Spouses WHERE userId = ? AND spouseUserId = ? AND spouseContactId = ?");
    if ($checkStmt === false) {
        Civi::log()->error("Check statement preparation failed: " . $this->db->error);
        return false;
    }

    if (!$checkStmt->bind_param('iii', $userId, $spouseUserId, $spouseContactId)) {
        Civi::log()->error("Binding parameters for check failed: " . $checkStmt->error);
        $checkStmt->close();
        return false;
    }

    if (!$checkStmt->execute()) {
        Civi::log()->error("Check execution failed: " . $checkStmt->error);
        $checkStmt->close();
        return false;
    }

    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        return true;
    }

    // Insert the relationship into the spouse table
    $insertStmt = $this->db->prepare("INSERT INTO Spouses (userId, spouseUserId, spouseContactId) VALUES (?, ?, ?)");
    
    if ($insertStmt === false) {
        Civi::log()->error("Insert statement preparation failed: " . $this->db->error);
        return false;
    }

    if (!$insertStmt->bind_param('iii', $userId, $spouseUserId, $spouseContactId)) {
        Civi::log()->error("Binding parameters for insert failed: " . $insertStmt->error);
        $insertStmt->close();
        return false;
    }

    if (!$insertStmt->execute()) {
        Civi::log()->error("Insert execution failed: " . $insertStmt->error);
        $insertStmt->close();
        return false;
    }

    $insertStmt->close();

    return true;
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
    return ts('Adding Relationship in Dovecare');
  }

  public function getDescription() {
    return ts('Add spouse relationship to Dovecare');
  }
}
?>