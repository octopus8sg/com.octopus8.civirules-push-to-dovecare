<?php

use CRM_Civirules_ExtensionUtil as E;
require_once(E::path('api/v3/CiviRulesAction.php'));

/**
 * Notification of deprecated function.
 *
 * @deprecated api notice
 * @return string
 *   to indicate this entire api entity is deprecated
 */
function _civicrm_api3_civi_rule_action_deprecation() {
  return 'The CiviRuleAction API is deprecated. Please use CiviRulesAction API4 instead (CiviRulesAction API3 is a direct replacement).';
}

/**
 * CiviRuleAction.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @deprecated
 */
function _civicrm_api3_civi_rule_action_create_spec(&$spec) {
  _civicrm_api3_civi_rules_action_create_spec($spec);
}

/**
 * CiviRuleAction.Create API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @deprecated
 */
function civicrm_api3_civi_rule_action_create($params) {
  return civicrm_api3_civi_rules_action_create($params);
}

/**
 * CiviRuleAction.Get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_action_get($params) {
  return civicrm_api3_civi_rules_action_get($params);
}

/**
 * CiviRuleAction.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @deprecated
 */
function _civicrm_api3_civi_rule_action_delete_spec(&$spec) {
  _civicrm_api3_civi_rules_action_delete_spec($spec);
}

/**
 * CiviRuleAction.Delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @deprecated
 */
function civicrm_api3_civi_rule_action_delete($params) {
  return civicrm_api3_civi_rules_action_delete($params);
}

/**
 * CiviRuleAction.process API
 *
 * Process delayed actions
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_action_process($params) {
  return civicrm_api3_civi_rules_action_process($params);
}

/**
 * CiviRuleAction.Cleanup API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @deprecated
 */
function _civicrm_api3_civi_rule_action_cleanup_spec(&$spec) {
  _civicrm_api3_civi_rules_action_cleanup_spec($spec);
}

/**
 * CiviRuleAction.Cleanup API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_action_cleanup($params) {
  return civicrm_api3_civi_rules_action_cleanup($params);
}
