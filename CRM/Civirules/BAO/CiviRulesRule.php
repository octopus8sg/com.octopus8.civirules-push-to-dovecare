<?php
/**
 * BAO Rule for CiviRule Rule
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_Civirules_BAO_CiviRulesRule extends CRM_Civirules_DAO_Rule {

  /**
   * Function to get values
   *
   * @return array $result found rows with data
   */
  public static function getValues($params) {
    $result = [];
    $rule = new CRM_Civirules_BAO_Rule();
    if (!empty($params)) {
      $fields = self::fields();
      foreach ($params as $key => $value) {
        if (isset($fields[$key])) {
          $rule->$key = $value;
        }
      }
    }
    $rule->find();
    while ($rule->fetch()) {
      $row = [];
      self::storeValues($rule, $row);
      // add trigger label
      if (isset($rule->trigger_id) && !empty($rule->trigger_id)) {
        $row['trigger'] = CRM_Civirules_BAO_Trigger::getTriggerLabelWithId($rule->trigger_id);
      }
      $result[$row['id']] = $row;
    }
    return $result;
  }

  /**
   * Deprecated add function
   *
   * @param array $params
   *
   * @return \CRM_Civirules_DAO_CiviRulesRule
   * @throws Exception when params is empty
   *
   * @deprecated
   */
  public static function add($params) {
    CRM_Core_Error::deprecatedFunctionWarning('writeRecord');
    return self::writeRecord($params);
  }

  /**
   * Function to delete a rule with id
   *
   * @param int $ruleId
   *
   * @throws Exception when ruleId is empty
   */
  public static function deleteWithId($ruleId) {
    if (empty($ruleId)) {
      throw new Exception(ts('rule id can not be empty when attempting to delete a civirule rule'));
    }
    CRM_Utils_Hook::pre('delete', 'CiviRuleRule', $ruleId, CRM_Core_DAO::$_nullArray);
    // also delete all references to the rule from logging if present
    if (self::checkTableExists('civirule_rule_log')) {
      $query = 'DELETE FROM civirule_rule_log WHERE rule_id = %1';
      CRM_Core_DAO::executeQuery($query, [1 => [$ruleId, 'Integer']]);
    }
    $rule = new CRM_Civirules_BAO_Rule();
    $rule->id = $ruleId;
    $rule->delete();
    CRM_Utils_Hook::post('delete', 'CiviRuleRule', $ruleId, CRM_Core_DAO::$_nullArray);
  }

  /**
   * Function to retrieve the label of a rule with ruleId
   *
   * @param int $ruleId
   *
   * @return string $rule->label
   */
  public static function getRuleLabelWithId($ruleId) {
    if (empty($ruleId)) {
      return '';
    }
    $rule = new CRM_Civirules_BAO_Rule();
    $rule->id = $ruleId;
    $rule->find(true);
    return $rule->label;
  }

  /**
   * Function to check if a label already exists in the rule table
   *
   * @param $labelToBeChecked
   * @return bool
   */
  public static function labelExists($labelToBeChecked) {
    $rule = new CRM_Civirules_BAO_Rule();
    $rule->label = $labelToBeChecked;
    if ($rule->count() > 0) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns an array with rules which should be triggered immediately
   *
   * @param string $objectName ObjectName in the Post hook
   * @param string $op op in the Post hook
   *
   * @return array
   */
  public static function findRulesByObjectNameAndOp($objectName, $op) {
    $triggers = [];
    $sql = "SELECT r.id AS rule_id, t.id AS trigger_id, t.class_name, r.trigger_params
            FROM `civirule_rule` r
            INNER JOIN `civirule_trigger` t ON r.trigger_id = t.id AND t.is_active = 1";
    // If $objectName is a Contact Type, also search for "Contact".
    if ($objectName == 'Individual' || $objectName == 'Organization' || $objectName == 'Household') {
      $sqlWhere = " WHERE r.`is_active` = 1 AND t.cron = 0 AND (t.object_name = %1 OR t.object_name = 'Contact') AND t.op = %2";
    }
    else {
      $sqlWhere = " WHERE r.`is_active` = 1 AND t.cron = 0 AND t.object_name = %1 AND t.op = %2";
    }
    $sql .= $sqlWhere;
    $params[1] = [$objectName, 'String'];
    $params[2] = [$op, 'String'];

    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      $triggerObject = CRM_Civirules_BAO_Trigger::getPostTriggerObjectByClassName($dao->class_name, FALSE);
      if ($triggerObject !== FALSE) {
        $triggerObject->setTriggerId($dao->trigger_id);
        $triggerObject->setRuleId($dao->rule_id);
        $triggerObject->setTriggerParams($dao->trigger_params ?? '');
        $triggers[] = $triggerObject;
      }
    }
    return $triggers;
  }

  /**
   * Returns an array with cron triggers which should be triggered in the cron
   *
   * @return array
   */
  public static function findRulesForCron() {
    $cronTriggers = [];
    $sql = "SELECT r.id AS rule_id, t.id AS trigger_id, t.class_name, r.trigger_params
            FROM `civirule_rule` r
            INNER JOIN `civirule_trigger` t ON r.trigger_id = t.id AND t.is_active = 1
            WHERE r.`is_active` = 1 AND t.cron = 1
            ORDER BY rule_id";

    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $cronTriggerObject = CRM_Civirules_BAO_Trigger::getTriggerObjectByClassName($dao->class_name, FALSE);
      if ($cronTriggerObject !== FALSE) {
        $cronTriggerObject->setTriggerId($dao->trigger_id);
        $cronTriggerObject->setRuleId($dao->rule_id);
        $cronTriggerObject->setTriggerParams($dao->trigger_params ?? '');
        $cronTriggers[] = $cronTriggerObject;
      }
    }
    return $cronTriggers;
  }

  /**
   * Returns an array with cron triggers which should be triggered in the cron
   *
   * @return array
   */
  public static function findRulesByClassname($classname) {
    $triggers = [];
    $sql = "SELECT r.id AS rule_id, t.id AS trigger_id, t.class_name, r.trigger_params
            FROM `civirule_rule` r
            INNER JOIN `civirule_trigger` t ON r.trigger_id = t.id AND t.is_active = 1
            WHERE r.`is_active` = 1 AND t.class_name = %1";
    $params[1] = [$classname, 'String'];
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      $triggerObject = CRM_Civirules_BAO_Trigger::getTriggerObjectByClassName($dao->class_name, FALSE);
      if ($triggerObject !== FALSE) {
        $triggerObject->setTriggerId($dao->trigger_id);
        $triggerObject->setRuleId($dao->rule_id);
        $triggerObject->setTriggerParams($dao->trigger_params ?? '');
        $triggers[] = $triggerObject;
      }
    }
    return $triggers;
  }

  /**
   * Function to get latest rule id
   *
   * @return int $ruleId
   */
  public static function getLatestRuleId() {
    $rule = new CRM_Civirules_BAO_Rule();
    $query = 'SELECT MAX(id) AS maxId FROM '.$rule->tableName();
    $dao = CRM_Core_DAO::executeQuery($query);
    if ($dao->fetch()) {
      return $dao->maxId;
    }
  }

  /**
   * Method to get all active rules with a specific trigger id
   *
   * @param int $triggerId
   *
   * @return array $ruleIds
   * @throws Exception when triggerId not integer
   */
  public static function getRuleIdsByTriggerId($triggerId) {
    if (!is_numeric($triggerId)) {
      throw new Exception(ts('You are passing a trigger id as a parameter into ') . __METHOD__
        . ts(' which does not pass the PHP is_numeric test. An integer is required (only numbers allowed)! Contact your system administrator'));
    }
    $ruleIds = [];
    $sql = 'SELECT id FROM civirule_rule WHERE trigger_id = %1 AND is_active = %2';
    $params = [
      1 => [$triggerId, 'Integer'],
      2 => [1, 'Integer']
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
    while ($dao->fetch()) {
      $ruleIds[] = $dao->id;
    }
    return $ruleIds;
  }

  /**
   * Method to determine if a rule is active on the civicrm queue
   *
   * @param $ruleId
   * @return bool
   */
  public static function isRuleOnQueue($ruleId) {
    $query = "SELECT * FROM civicrm_queue_item WHERE queue_name = %1";
    $dao = CRM_Core_DAO::executeQuery($query, [1 => ["org.civicoop.civirules.action", "String"]]);
    while ($dao->fetch()) {
      if (isset($dao->data)) {
        $queueItemData = @unserialize($dao->data);
        if ($queueItemData) {
          foreach($queueItemData->arguments as $dataArgument) {
            if (is_subclass_of($dataArgument, 'CRM_Civirules_Action')) {
              if ($dataArgument->getRuleId() == $ruleId) {
                return TRUE;
              }
            }
          }
        }
      }
    }
    return FALSE;
  }
}
