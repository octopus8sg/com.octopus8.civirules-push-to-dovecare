<?php

class CRM_Civirules_TriggerData_Cron extends CRM_Civirules_TriggerData_TriggerData {

  /**
   * The Entity Name (CamelCase eg. Membership, Contact)
   *
   * @var string
   */
  protected string $entityName;

  /**
   * @param int $contactId
   * @param string $entity
   * @param array $data
   * @param int|NULL $entity_id
   */
  public function __construct($contactId, $entity, $data, $entity_id = NULL) {
    parent::__construct();

    $this->entityName = $entity;
    if (isset($entity_id)) {
      $this->setEntityId($entity_id);
    } elseif (isset($data['id'])) {
      $this->setEntityId($data['id']);
    }
    else {
      \Civi::log('civirules')->warning('CRM_Civirules_TriggerData_Cron missing entityID for entity: ' . $entity);
    }
    $this->setContactId($contactId);
    $this->setEntityData($entity, $data);
  }

  /**
   * @return string
   */
  public function getEntity(): string {
    return $this->entityName;
  }

}
