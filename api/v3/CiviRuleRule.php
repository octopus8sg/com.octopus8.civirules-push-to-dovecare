<?php

use CRM_Civirules_ExtensionUtil as E;
require_once(E::path('api/v3/CiviRulesRule.php'));

/**
 * Notification of deprecated function.
 *
 * @deprecated api notice
 * @return string
 *   to indicate this entire api entity is deprecated
 * @deprecated
*/
function _civicrm_api3_civi_rule_rule_deprecation() {
  return 'The CiviRuleRule API is deprecated. Please use CiviRulesRule API4 instead (CiviRulesRule API3 is a direct replacement).';
}

/**
 * CiviRuleRule.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
*/
function _civicrm_api3_civi_rule_rule_create_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_create_spec($spec);
}

/**
 * CiviRuleRule.Create API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @deprecated
*/
function civicrm_api3_civi_rule_rule_create($params) {
  return civicrm_api3_civi_rules_rule_create($params);
}

/**
 * CiviRuleRule.Get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 * @deprecated
*/
function civicrm_api3_civi_rule_rule_get($params) {
  return civicrm_api3_civi_rules_rule_get($params);
}


/**
 * CiviRuleRule.Delete API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
*/
function _civicrm_api3_civi_rule_rule_delete_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_delete_spec($spec);
}

/**
 * CiviRuleRule.Delete API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 * @deprecated
*/
function civicrm_api3_civi_rule_rule_delete($params) {
  return civicrm_api3_civi_rules_rule_delete($params);
}

/**
 * CiviRuleRule.Clone API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
*/
function _civicrm_api3_civi_rule_rule_clone_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_clone_spec($spec);
}

/**
 * CiviRuleRule.Clone API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 * @deprecated
*/
function civicrm_api3_civi_rule_rule_clone($params) {
  return civicrm_api3_civi_rules_rule_clone($params);
}

/**
 * CiviRuleRule.GetClones API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
*/
function _civicrm_api3_civi_rule_rule_getclones_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_getclones_spec($spec);
}

/**
 * CiviRuleRule.GetClones API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_error
 * @see civicrm_api3_create_success
 * @deprecated
*/
function civicrm_api3_civi_rule_rule_getclones($params) {
  return civicrm_api3_civi_rules_rule_getclones($params);
}

/**
 * CiviRuleRule.Trigger API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @return void
 * @deprecated
*/
function _civicrm_api3_civi_rule_rule_trigger_spec(&$spec) {
  _civicrm_api3_civi_rules_rule_trigger_spec($spec);
}

/**
 * CiviRuleRule.Trigger API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @throws \CRM_Core_Exception
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @todo Make this work with non "Post" triggers? Eg. functions like getObjectName(),getOp() are only defined on CRM_Civirules_Trigger_Post
 * @deprecated
*/
function civicrm_api3_civi_rule_rule_trigger($params) {
  return civicrm_api3_civi_rules_rule_trigger($params);
}
