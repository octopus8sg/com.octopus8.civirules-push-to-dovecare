<?php
class CRM_CivirulesActions_Activity_addingcfsvalue extends CRM_Civirules_Action {
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
    
    $checkStmt = $this->db->prepare("SELECT COUNT(*) FROM Users WHERE contactId = ?");
    if ($checkStmt === false) {
        Civi::log()->error("Prepare failed: " . $this->db->error);
        return false;
    }

    if (!$checkStmt->bind_param('s', $contactId)) {
        Civi::log()->error("Bind failed: " . $checkStmt->error);
        $checkStmt->close();
        return false;
    }

    if (!$checkStmt->execute()) {
        Civi::log()->error("Execute failed: " . $checkStmt->error);
        $checkStmt->close();
        return false;
    }

    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    // Fetch the CFS value from the API
    $cfsurl = $this->config['api']['url'] . '/Activity/get';
    $cfsparams = [
      'select' => ['Mandatory_Intervention_Record.CFS_Clinical_Frailty_Scores'],
      'where' => [['activity_type_id', '=', 71], ['target_contact_id', 'CONTAINS', $contactId]],
      'orderBy' => ['id' => 'DESC'],
      'limit' => 1,
    ];
    $cfsrequest = stream_context_create([
      'http' => [
        'method' => 'POST',
        'header' => [
          'Content-Type: application/x-www-form-urlencoded',
          'X-Civi-Auth: Bearer ' . $this->config['api']['key'],
        ],
        'content' => http_build_query(['params' => json_encode($cfsparams)]),
      ],
      'ssl' => [
          'verify_peer' => false,
          'verify_peer_name' => false,
      ]
    ]);
    $cfsResponse = file_get_contents($cfsurl, FALSE, $cfsrequest);
    if ($cfsResponse === FALSE) {
        Civi::log()->error("Failed to fetch contact data from API");
        return false;
    }
    
    $cfscontact = json_decode($cfsResponse, TRUE);
    if (json_last_error() !== JSON_ERROR_NONE) {
        Civi::log()->error("JSON decode error: " . json_last_error_msg());
        return false;
    }

    $cfs = $cfscontact['values'][0]['Mandatory_Intervention_Record.CFS_Clinical_Frailty_Scores'];

    if ($count > 0) {
        // Update existing record
        Civi::log()->info("Contact ID: $contactId exists. Updating CFS value.");
        $stmt = $this->db->prepare("UPDATE users SET CFS = ? WHERE contactId = ?");
        if ($stmt === false) {
            Civi::log()->error("Prepare failed: " . $this->db->error);
            return false;
        }

        if (!$stmt->bind_param('ss', $cfs, $contactId)) {
            Civi::log()->error("Bind failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    } else {
        // Insert new record
        Civi::log()->info("Contact ID: $contactId does not exist. Inserting new record.");
        $stmt = $this->db->prepare("INSERT INTO users (contactId, CFS) VALUES (?, ?)");
        if ($stmt === false) {
            Civi::log()->error("Prepare failed: " . $this->db->error);
            return false;
        }

        if (!$stmt->bind_param('ss', $contactId, $cfs)) {
            Civi::log()->error("Bind failed: " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    if ($stmt->execute()) {
        Civi::log()->info("CFS value processed successfully for contact ID: $contactId");
    } else {
        Civi::log()->error("Execute failed: " . $stmt->error);
    }

    $stmt->close();
    return true; // Return true if the action was successful
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
    return ts('Adding CFS Value');
  }

  public function getDescription() {
    return ts('Add or update CFS value in the database');
  }
}
?>