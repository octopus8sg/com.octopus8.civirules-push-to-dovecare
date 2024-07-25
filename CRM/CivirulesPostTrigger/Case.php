<?php

class CRM_CivirulesPostTrigger_Case extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Case');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Case_DAO_Case';
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
    $t = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);

    if ($op == 'create') {
      $this->setTriggerData(clone $t);
      parent::triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID);
    }

    //trigger for each client
    $clients = CRM_Case_BAO_Case::getCaseClients($objectId);
    foreach($clients as $client) {
      $triggerData = clone $t;
      $triggerData->setEntityData('Relationship', NULL);
      $triggerData->setContactId($client);
      $this->setTriggerData($triggerData);
      parent::triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID);
    }

    //trigger for each case role
    $relatedContacts = CRM_Case_BAO_Case::getRelatedContacts($objectId);
    foreach($relatedContacts as $contact) {
      $triggerData = clone $t;
      $relationshipData = NULL;
      $relationship = new CRM_Contact_BAO_Relationship();
      $relationship->contact_id_b = $contact['contact_id'];
      $relationship->case_id = $objectId;
      if ($relationship->find(true)) {
        CRM_Core_DAO::storeValues($relationship, $relationshipData);
      }
      $triggerData->setEntityData('Relationship', $relationshipData);
      $triggerData->setContactId($contact['contact_id']);
      $this->setTriggerData($triggerData);
      parent::triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID);
    }
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Relationship', 'Relationship', 'CRM_Contact_DAO_Relationship' , 'Relationship');
    return $entities;
  }

  protected function convertObjectRefToDataArray($entity, $objectRef, $id) {
    //set data
    $data = parent::convertObjectRefToDataArray($entity, $objectRef, $id);

    //retrieve extra data from the database because the objectRef does not contain all
    //data from the case
    $case_data = civicrm_api3('Case', 'getsingle', array('id' => $id));
    foreach($case_data as $key => $value) {
      if (!isset($data[$key])) {
        $data[$key] = $value;
      }
    }

    //unset contact_id
    unset($data['contact_id']);

    return $data;
  }

}
