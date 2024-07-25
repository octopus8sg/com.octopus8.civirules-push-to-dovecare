<?php

class CRM_CivirulesPostTrigger_ActionLog extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'ActionLog');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Core_DAO_ActionLog';
  }

  /**
   * Get trigger data belonging to this specific post event
   *
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   * @return CRM_Civirules_TriggerData_Edit|CRM_Civirules_TriggerData_Post
   */
  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID = NULL) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);
    $actionLogData = $triggerData->getEntityData('ActionLog');
    $actionLog = CRM_Core_BAO_ActionLog::findById($actionLogData['id']);
    $triggerData->setContactId($actionLog->contact_id);
    return $triggerData;
  }

}

