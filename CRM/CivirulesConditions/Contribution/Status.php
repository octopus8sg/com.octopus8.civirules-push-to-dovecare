<?php

class CRM_CivirulesConditions_Contribution_Status extends CRM_CivirulesConditions_Generic_Status {

  /**
   * The entity name (eg. Membership)
   * @return string
   */
  protected function getEntity() {
    return 'Contribution';
  }

  /**
   * The entity status field (eg. membership_status_id)
   * @return string
   */
  public function getEntityStatusFieldName() {
    return 'contribution_status_id';
  }

  /**
   * Returns an array of statuses as [ id => label ]
   * @param bool $active
   * @param bool $inactive
   *
   * @return array
   */
  public static function getEntityStatusList($active = TRUE, $inactive = FALSE) {
    return parent::getEntityStatusListFromOptionGroup('contribution_status', $active, $inactive);
  }

  /**
   * Returns condition data as an array and ready for export.
   * E.g. replace ids for names.
   *
   * @return array
   */
  public function exportConditionParameters() {
    $params = parent::exportConditionParameters();
    if (!empty($params['status_id']) && is_array($params['status_id'])) {
      foreach($params['status_id'] as $i => $j) {
        $params['status_id'][$i] = civicrm_api3('OptionValue', 'getvalue', [
          'return' => 'name',
          'value' => $j,
          'option_group_id' => 'contribution_status',
        ]);
      }
    } elseif (!empty($params['status_id'])) {
      try {
        $params['status_id'] = civicrm_api3('OptionValue', 'getvalue', [
          'return' => 'name',
          'value' => $params['status_id'],
          'option_group_id' => 'contribution_status',
        ]);
      } catch (\CiviCRM_Api3_Exception $e) {
        // Do nothing.
      }
    }
    return $params;
  }

  /**
   * Returns condition data as an array and ready for import.
   * E.g. replace name for ids.
   *
   * @return string
   */
  public function importConditionParameters($condition_params = NULL) {
    if (!empty($condition_params['status_id']) && is_array($condition_params['status_id'])) {
      foreach($condition_params['status_id'] as $i => $j) {
        $condition_params['status_id'][$i] = civicrm_api3('OptionValue', 'getvalue', [
          'return' => 'value',
          'name' => $j,
          'option_group_id' => 'contribution_status',
        ]);
      }
    } elseif (!empty($condition_params['status_id'])) {
      try {
        $condition_params['status_id'] = civicrm_api3('OptionValue', 'getvalue', [
          'return' => 'value',
          'name' => $condition_params['status_id'],
          'option_group_id' => 'contribution_status',
        ]);
      } catch (\CiviCRM_Api3_Exception $e) {
        // Do nothing.
      }
    }
    return parent::importConditionParameters($condition_params);
  }

}
