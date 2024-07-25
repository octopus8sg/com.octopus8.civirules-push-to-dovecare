<?php

/**
 * CiviRulesRuleTag.Get API
 *
 * @param array $params
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws \CRM_Core_Exception
 */
function civicrm_api3_civi_rules_rule_tag_get($params) {
  $returnValues = CRM_Civirules_BAO_CiviRulesRuleTag::getValues($params);
  return civicrm_api3_create_success($returnValues, $params, 'CiviRulesRuleTag', 'Get');
}

