<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesCronTrigger_ActivityDate extends CRM_CivirulesCronTrigger_Activity {

  /**
   * Method to query trigger entities
   */
  protected function queryForTriggerEntities() {
    if (empty($this->triggerParams['activity_type_id'])) {
      return false;
    }
    if (empty($this->triggerParams['activity_status_id'])) {
      return false;
    }

    $activityContactWhereClause = '';
    if (!empty($this->triggerParams['record_type'])) {
      $activityContactWhereClause = "AND `ac`.`record_type_id` = %5";
      $params[5] = [$this->triggerParams['record_type'], 'Integer'];
    }

    $activityCaseWhereClause = 'AND `ca`.`case_id` IS NULL';
    if (!empty($this->triggerParams['case_activity'])) {
      $activityCaseWhereClause = 'AND `ca`.`case_id` IS NOT NULL';
    }

    $sql = "SELECT a.*, ac.contact_id as contact_id, ac.record_type_id as record_type_id, ac.id as activity_contact_id, ca.case_id as case_id
            FROM `civicrm_activity` `a`
            INNER JOIN `civicrm_activity_contact` ac ON a.id = ac.activity_id
            LEFT JOIN `civicrm_case_activity` ca ON a.id = ca.activity_id
            LEFT JOIN `civirule_rule_log` `rule_log` ON `rule_log`.entity_table = 'civicrm_activity' AND `rule_log`.entity_id = a.id AND `rule_log`.`contact_id` = `ac`.`contact_id` AND DATE(`rule_log`.`log_date`) = DATE(NOW())  AND `rule_log`.`rule_id` = %3
            WHERE `a`.`activity_type_id` = %1 AND a.status_id = %2 AND a.activity_date_time <= NOW()
            AND `a`.`is_deleted` = 0
            AND `rule_log`.`id` IS NULL
            {$activityContactWhereClause}
            {$activityCaseWhereClause}
            AND `ac`.`contact_id` NOT IN (
              SELECT `rule_log2`.`contact_id`
              FROM `civirule_rule_log` `rule_log2`
              WHERE `rule_log2`.`rule_id` = %3 AND DATE(`rule_log2`.`log_date`) = DATE(NOW()) and `rule_log2`.`entity_table` IS NULL AND `rule_log2`.`entity_id` IS NULL
            )";
    $params[1] = array($this->triggerParams['activity_type_id'], 'Integer');
    $params[2] = array($this->triggerParams['activity_status_id'], 'Integer');
    $params[3] = array($this->ruleId, 'Integer');
    $this->activityDAO = CRM_Core_DAO::executeQuery($sql, $params, true, 'CRM_Activity_DAO_Activity');

    return true;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   *
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/activitydate', 'rule_id='.$ruleId);
  }

  /**
   * Returns a description of this trigger
   *
   * @return string
   */
  public function getTriggerDescription(): string {
    $activityTypeLabel = CRM_Civirules_Utils::getOptionLabelWithValue(CRM_Civirules_Utils::getOptionGroupIdWithName('activity_type'),  $this->triggerParams['activity_type_id']);
    $activityStatusLabel = CRM_Civirules_Utils::getOptionLabelWithValue(CRM_Civirules_Utils::getOptionGroupIdWithName('activity_status'),  $this->triggerParams['activity_status_id']);

    $result = civicrm_api3('ActivityContact', 'getoptions', [
      'field' => "record_type_id",
    ]);
    $options[0] = E::ts('All contacts');
    $options = array_merge($options, $result['values']);

    $caseActivity = 'Not case activity';
    if (!empty($this->triggerParams['case_activity'])) {
      $caseActivity = 'Case activity';
    }

    return ts('%4 with type %1 and status %2 date reached. Trigger for %3', [
      1 => $activityTypeLabel,
      2 => $activityStatusLabel,
      3 => $options[$this->triggerParams['record_type']],
      4 => $caseActivity,
    ]);
  }

}
