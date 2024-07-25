<?php

use CRM_Civirules_ExtensionUtil as E;
require_once(E::path('api/v3/CiviRulesRuleAction.php'));

/**
 * Notification of deprecated function.
 *
 * @deprecated api notice
 * @return string
 *   to indicate this entire api entity is deprecated
 * @deprecated
 */
function _civicrm_api3_civi_rule_rule_action_deprecation() {
  return 'The CiviRuleRuleAction API is deprecated. Please use CiviRulesRuleAction API4 instead (CiviRulesRuleAction API3 is a direct replacement).';
}

/**
 * CiviRuleRuleAction.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 */
function _civicrm_api3_civi_rule_rule_action_create_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_action_create_spec($spec);
}

/**
 * CiviRuleRuleAction.Create API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_civi_rule_rule_action_Create($params) {
  return civicrm_api3_civi_rules_rule_action_create($params);
}

/**
 * CiviRuleRuleAction.Get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 */
function civicrm_api3_civi_rule_rule_action_get($params) {
  return civicrm_api3_civi_rules_rule_action_get($params);
}

/**
 * CiviRuleRuleAction.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 */
function _civicrm_api3_civi_rule_rule_action_delete_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_action_delete_spec($spec);
}

/**
 * CiviRuleRuleAction.Delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 */
function civicrm_api3_civi_rule_rule_action_delete($params) {
  return civicrm_api3_civi_rules_rule_action_delete($params);
}


