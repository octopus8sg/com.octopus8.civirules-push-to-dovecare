<?php

use CRM_Civirules_ExtensionUtil as E;
require_once(E::path('api/v3/CiviRulesTrigger.php'));

/**
 * Notification of deprecated function.
 *
 * @deprecated api notice
 * @return string
 *   to indicate this entire api entity is deprecated
 */
function _civicrm_api3_civi_rule_trigger_deprecation() {
  return 'The CiviRuleTrigger API is deprecated. Please use CiviRulesTrigger API4 instead (CiviRulesTrigger API3 is a direct replacement).';
}

/**
 * CiviRuleTrigger.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
 */
function _civicrm_api3_civi_rule_trigger_create_spec(&$spec) {
  _civicrm_api3_civi_rules_trigger_create_spec($spec);
}

/**
 * CiviRuleTrigger.Create API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @deprecated
 */
function civicrm_api3_civi_rule_trigger_create($params) {
  return civicrm_api3_civi_rules_trigger_create($params);
}


/**
 * CiviRuleTrigger.Get API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_trigger_get($params) {
  return civicrm_api3_civi_rules_trigger_get($params);
}

/**
 * CiviRuleTrigger.Delete API specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
 */
function _civicrm_api3_civi_rule_trigger_delete_spec(&$spec) {
  _civicrm_api3_civi_rules_trigger_delete_spec($spec);
}

/**
 * CiviRuleTrigger.Delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_trigger_delete($params) {
  return civicrm_api3_civi_rules_trigger_delete($params);
}

/**
 * CiviRuleTrigger.Cleanup API specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
 */
function _civicrm_api3_civi_rule_trigger_cleanup_spec(&$spec) {
  _civicrm_api3_civi_rules_trigger_cleanup_spec($spec);
}

/**
 * CiviRuleTrigger.Cleanup API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 * @deprecated
 */
function civicrm_api3_civi_rule_trigger_cleanup($params) {
  return civicrm_api3_civi_rules_trigger_cleanup($params);
}


