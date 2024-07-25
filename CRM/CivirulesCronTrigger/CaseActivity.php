<?php

/**
 * Daily trigger for case activity
 */
class CRM_CivirulesCronTrigger_CaseActivity extends CRM_Civirules_Trigger_Cron {

  private $dao = FALSE;

  /**
   * This function returns a CRM_Civirules_TriggerData_TriggerData this entity is used for triggering the rule
   *
   * Return false when no next entity is available
   *
   * @return CRM_Civirules_TriggerData_TriggerData|false
   */
  protected function getNextEntityTriggerData() {
    if (!$this->dao) {
      if (!$this->queryForTriggerEntities()) {
        return FALSE;
      }
    }
    if ($this->dao->fetch()) {
      $data = [];
      CRM_Core_DAO::storeValues($this->dao, $data);
      $triggerData = new CRM_Civirules_TriggerData_Cron(0, 'Case', $data);
      return $triggerData;
    }
    return FALSE;
  }

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition('Case', 'Case', 'CRM_Case_DAO_Case', 'Case');
  }

  /**
   * Method to query trigger entities
   *
   */
  private function queryForTriggerEntities() {
    $sql = "SELECT c.*
            FROM `civicrm_case` `c`
            WHERE `c`.`is_deleted` = 0
            ";
    $this->dao = CRM_Core_DAO::executeQuery($sql, [], TRUE, 'CRM_Case_DAO_Case');

    return TRUE;
  }
}
