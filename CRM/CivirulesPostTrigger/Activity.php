<?php

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesPostTrigger_Activity extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Activity');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Activity_DAO_Activity';
  }

  /**
   * Trigger a rule for this trigger
   *
   * @param string $op
   * @param string $objectName
   * @param int $objectId
   * @param object $objectRef
   * @param string $eventID
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID) {
    $triggerData = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);
    if (empty($triggerData->getEntityId())) {
      $triggerData->setEntityId($objectId);
    }
    //trigger for activity trigger for every source_contact_id, target_contact_id and assignee_contact_id
    $activityContacts = array();
    if ($op == 'delete') {
      $preData = CRM_Civirules_Utils_PreData::getPreData($objectName, $objectId, $eventID);
      if (isset($preData['activity_contacts'])) {
        $activityContacts = $preData['activity_contacts'];
      }
    } else {
      $activityContact = new CRM_Activity_BAO_ActivityContact();
      $activityContact->activity_id = $objectId;
      if ($this->triggerParams && isset($this->triggerParams['record_type']) && $this->triggerParams['record_type']) {
        $activityContact->record_type_id = $this->triggerParams['record_type'];
      }
      $activityContact->find();
      while ($activityContact->fetch()) {
        $data = [];
        CRM_Core_DAO::storeValues($activityContact, $data);
        $activityContacts[] = $data;
      }
    }

    foreach($activityContacts as $activityContact) {
      $triggerData->setEntityData('ActivityContact', $activityContact);
      if (isset($activityContact['contact_id']) && $activityContact['contact_id']) {
        $triggerData->setContactId($activityContact['contact_id']);
      }
      $this->setTriggerData($triggerData);
      parent::triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID);
    }
  }

  /**
   * Alter the pre data
   *
   * Could be overriden by child classes.
   *
   * @param $data
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $params
   * @param $eventID
   *
   * @return mixed
   */
  public function alterPreData($data, $op, $objectName, $objectId, $params, $eventID) {
    $activityContacts = [];
    $activityContact = new CRM_Activity_BAO_ActivityContact();
    $activityContact->activity_id = $objectId;
    if ($this->triggerParams && isset($this->triggerParams['record_type']) && $this->triggerParams['record_type']) {
      $activityContact->record_type_id = $this->triggerParams['record_type'];
    }
    $activityContact->find();
    while ($activityContact->fetch()) {
      CRM_Core_DAO::storeValues($activityContact, $data);
      $activityContacts[] = $data;
    }
    $data['activity_contacts'] = $activityContacts;
    return $data;
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('ActivityContact', 'ActivityContact', 'CRM_Activity_DAO_ActivityContact' , 'ActivityContact');
    return $entities;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a trigger
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/activity', 'rule_id='.$ruleId);
  }

  /**
   * Returns a description of this trigger
   *
   * @return string
   */
  public function getTriggerDescription(): string {
    $result = civicrm_api3('ActivityContact', 'getoptions', [
      'field' => "record_type_id",
    ]);
    $options[0] = E::ts('For all contacts');
    foreach($result['values'] as $val => $opt) {
      $options[$val] = $opt;
    }
    return E::ts('Trigger for %1', array(1=>$options[$this->triggerParams['record_type']]));
  }

}
