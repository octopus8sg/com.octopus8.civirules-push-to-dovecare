<?php

use CRM_Civirules_ExtensionUtil as E;
require_once(E::path('api/v3/CiviRulesRuleCondition.php'));

/**
 * Notification of deprecated function.
 *
 * @deprecated api notice
 * @return string
 *   to indicate this entire api entity is deprecated
 * @deprecated
 */
function _civicrm_api3_civi_rule_rule_condition_deprecation() {
  return 'The CiviRuleRuleCondition API is deprecated. Please use CiviRulesRuleCondition API4 instead (CiviRulesRuleCondition API3 is a direct replacement).';
}

/**
 * CiviRuleRuleCondition.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
 */
function _civicrm_api3_civi_rule_rule_condition_create_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_condition_create_spec($spec);
}

/**
 * CiviRuleRuleCondition.Create API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_rule_condition_create($params) {
  return civicrm_api3_civi_rules_rule_condition_create($params);
}

/**
 * CiviRuleRuleCondition.Get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 * @deprecated
 */
function civicrm_api3_civi_rule_rule_condition_get($params) {
  return civicrm_api3_civi_rules_rule_condition_get($params);
}

/**
 * CiviRulesRuleAction.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
 */
function _civicrm_api3_civi_rule_rule_condition_delete_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_condition_delete_spec($spec);
}

/**
 * CiviRuleRuleCondition.Delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @deprecated
 */
function civicrm_api3_civi_rule_rule_condition_delete($params) {
  return civicrm_api3_civi_rules_rule_condition_delete($params);
}
