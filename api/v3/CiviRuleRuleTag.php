<?php

use CRM_Civirules_ExtensionUtil as E;
require_once(E::path('api/v3/CiviRulesRuleTag.php'));

/**
 * Notification of deprecated function.
 *
 * @deprecated api notice
 * @return string
 *   to indicate this entire api entity is deprecated
 */
function _civicrm_api3_civi_rule_rule_tag_deprecation() {
  return 'The CiviRuleRuleTag API is deprecated. Please use CiviRulesRuleTag API4 instead (CiviRulesRuleTag API3 is a direct replacement).';
}

/**
 * CiviRuleRuleTag.Get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_civi_rule_rule_tag_get($params) {
  return civicrm_api3_civi_rules_rule_tag_get($params);
}

