<?php

class CRM_CivirulesPostTrigger_Contact extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Contact');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Contact_DAO_Contact';
  }

  /**
   * Checks whether the trigger provides a certain entity.
   *
   * @param string $entity
   *
   * @return bool
   */
  public function doesProvideEntity(string $entity): bool {
    if ($entity == 'Contact') {
      return TRUE;
    }
    $availableEntities = $this->getProvidedEntities();
    foreach($availableEntities as $providedEntity) {
      if (strtolower($providedEntity->entity) == strtolower($entity)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Checks whether the trigger provides a certain set of entities
   *
   * @param array<string> $entities
   *
   * @return bool
   */
  public function doesProvideEntities($entities): bool {
    $availableEntities = $this->getProvidedEntities();
    foreach($entities as $entity) {
      $entityPresent = false;
      if ($entity == 'Contact') {
        $entityPresent = true;
      } else {
        foreach ($availableEntities as $providedEntity) {
          if (strtolower($providedEntity->entity) == strtolower($entity)) {
            $entityPresent = TRUE;
          }
        }
      }
      if (!$entityPresent) {
        return false;
      }
    }
    return true;
  }

}
