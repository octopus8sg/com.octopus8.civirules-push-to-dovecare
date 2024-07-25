<?php
use CRM_Civirules_ExtensionUtil as E;

class CRM_Civirules_BAO_CiviRulesRuleLog extends CRM_Civirules_DAO_RuleLog {

  /**
   * Create a new RuleLog based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Civirules_DAO_RuleLog|NULL
   *
  public static function create($params) {
    $className = 'CRM_Civirules_DAO_RuleLog';
    $entityName = 'RuleLog';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
