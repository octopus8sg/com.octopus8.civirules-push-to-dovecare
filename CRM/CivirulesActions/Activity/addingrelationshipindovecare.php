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

    // Fetch the user ID for the original contact
    $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
    if ($query === false) {
        return false;
    }

    if (!$query->bind_param('i', $contactId)) {
        $query->close();
        return false;
    }

    if (!$query->execute()) {
        $query->close();
        return false;
    }

    $query->bind_result($userId);
    if (!$query->fetch()) {
        $query->close();
        return false;
    }
    $query->close();

    // Fetch the user ID for the spouse contact
    $query = $this->db->prepare("SELECT id FROM Users WHERE contactId = ?");
    if ($query === false) {
        return false;
    }

    if (!$query->bind_param('i', $spouseContactId)) {
        $query->close();
        return false;
    }

    if (!$query->execute()) {
        $query->close();
        return false;
    }

    $query->bind_result($spouseUserId);
    if (!$query->fetch()) {
        $query->close();
        return false;
    }
    $query->close();

    // Update the database with the spouse's contact ID and user ID
    $updateStmt = $this->db->prepare("UPDATE Users SET spouseContactId = ?, spouseUserId = ? WHERE contactId = ?");
    
    if ($updateStmt === false) {
        return false;
    }

    if (!$updateStmt->bind_param('iii', $spouseContactId, $spouseUserId, $contactId)) {
        $updateStmt->close();
        return false;
    }

    if (!$updateStmt->execute()) {
        $updateStmt->close();
        return false;
    }

    $updateStmt->close();

    // Now, also update the spouse's record with the original contact ID and user ID
    $updateStmt = $this->db->prepare("UPDATE Users SET spouseContactId = ?, spouseUserId = ? WHERE contactId = ?");
    
    if ($updateStmt === false) {
        return false;
    }

    if (!$updateStmt->bind_param('iii', $contactId, $userId, $spouseContactId)) {
        $updateStmt->close();
        return false;
    }

    if (!$updateStmt->execute()) {
        $updateStmt->close();
        return false;
    }

    $updateStmt->close();

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
