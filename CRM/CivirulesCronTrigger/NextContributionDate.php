<?php
/**
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesCronTrigger_NextContributionDate extends CRM_Civirules_Trigger_Cron {

  /**
   * @var \CRM_Member_DAO_Membership $dao
   */
  private $_dao = NULL;

  public static function intervals() {
    return [
      '-days' => ts('Day(s) before next scheduled recurring contribution date'),
      '-weeks' => ts('Week(s) before next scheduled recurring contribution date'),
      '-months' => ts('Month(s) before next scheduled recurring contribution date'),
      '+days' => ts('Day(s) after next scheduled recurring contribution date'),
      '+weeks' => ts('Week(s) after next scheduled recurring contribution date'),
      '+months' => ts('Month(s) after next scheduled recurring contribution date'),
    ];
  }

  /**
   * This function returns a CRM_Civirules_TriggerData_TriggerData this entity is used for triggering the rule
   * Return false when no next entity is available
   *
   * @return CRM_Civirules_TriggerData_TriggerData|false
   */
  protected function getNextEntityTriggerData() {
    if (!$this->_dao) {
      if (!$this->queryForTriggerEntities()) {
        return FALSE;
      }
    }
    if ($this->_dao->fetch()) {
      $data = [];
      CRM_Core_DAO::storeValues($this->_dao, $data);
      return new CRM_Civirules_TriggerData_Cron($this->_dao->contact_id, 'ContributionRecur', $data, $data['contribution_recur_id']);
    }
    return FALSE;
  }

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition(ts('Recurring Contribution'), 'ContributionRecur', 'CRM_Contribute_DAO_ContributionRecur', 'ContributionRecur');
  }

  /**
   * Method to query trigger entities
   */
  private function queryForTriggerEntities() {
    $nextDateStatement = "AND DATE(r.next_sched_contribution_date) = CURRENT_DATE()";
    $params = [1 => [0, "Integer"]];
    switch ($this->triggerParams['interval_unit']) {
      case '-days':
        $nextDateStatement = "AND DATE_SUB(r.next_sched_contribution_date, INTERVAL %2 DAY) = CURRENT_DATE()";
        $params[2] = [$this->triggerParams['interval'], 'Integer'];
        break;
      case '-weeks':
        $nextDateStatement = "AND DATE_SUB(r.next_sched_contribution_date, INTERVAL %2 WEEK) = CURRENT_DATE()";
        $params[2] = [$this->triggerParams['interval'], 'Integer'];
        break;
      case '-months':
        $nextDateStatement = "AND DATE_SUB(r.next_sched_contribution_date, INTERVAL %2 MONTH) = CURRENT_DATE()";
        $params[2] = [$this->triggerParams['interval'], 'Integer'];
        break;
      case '+days':
        $nextDateStatement = "AND DATE_ADD(r.next_sched_contribution_date, INTERVAL %2 DAY) = CURRENT_DATE()";
        $params[2] = [$this->triggerParams['interval'], 'Integer'];
        break;
      case '+weeks':
        $nextDateStatement = "AND DATE_ADD(r.next_sched_contribution_date, INTERVAL %2 WEEK) = CURRENT_DATE()";
        $params[2] = [$this->triggerParams['interval'], 'Integer'];
        break;
      case '+months':
        $nextDateStatement = "AND DATE_ADD(r.next_sched_contribution_date, INTERVAL %2 MONTH) = CURRENT_DATE()";
        $params[2] = [$this->triggerParams['interval'], 'Integer'];
        break;
    }

    $sql = "SELECT r.id AS `contribution_recur_id`, r.*
            FROM `civicrm_contribution_recur` `r`
            LEFT JOIN `civirule_rule_log` `rule_log` ON `rule_log`.entity_table = 'civicrm_contribution_recur' AND `rule_log`.entity_id = r.id AND `rule_log`.`contact_id` = `r`.`contact_id` AND DATE(`rule_log`.`log_date`) = DATE(NOW()) AND `rule_log`.`rule_id` = %3
            WHERE `r`.`is_test` = %1 AND (`r`.`next_sched_contribution_date` > CURRENT_DATE() OR `r`.`next_sched_contribution_date` IS NULL)
            AND `rule_log`.`id` IS NULL
            {$nextDateStatement}
            AND `r`.`contact_id` NOT IN (
              SELECT `rule_log2`.`contact_id`
              FROM `civirule_rule_log` `rule_log2`
              WHERE `rule_log2`.`rule_id` = %3 AND DATE(`rule_log2`.`log_date`) = DATE(NOW()) and `rule_log2`.`entity_table` IS NULL AND `rule_log2`.`entity_id` IS NULL
            )";
    $params[3] = [$this->ruleId, 'Integer'];
    $this->_dao = CRM_Core_DAO::executeQuery($sql, $params, TRUE, 'CRM_Contribute_DAO_ContributionRecur');
    return TRUE;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/nextcontributiondate/', 'rule_id='.$ruleId);
  }

  /**
   * Returns a description of this trigger
   *
   * @return string
   */
  public function getTriggerDescription(): string {
    $intervalUnits = self::intervals();
    $intervalUnitLabel = $intervalUnits[$this->triggerParams['interval_unit']];
    return E::ts('Next Scheduled Contribution Date %1 - %2', [
      1 => $this->triggerParams['interval'],
      2 => $intervalUnitLabel,
    ]);
  }

  /**
   * Returns additional entities provided in this trigger.
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    return parent::getAdditionalEntities();
  }

}
