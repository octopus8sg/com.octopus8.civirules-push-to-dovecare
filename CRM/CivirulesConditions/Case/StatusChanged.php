<?php

class CRM_CivirulesConditions_Case_StatusChanged extends CRM_CivirulesConditions_Generic_FieldValueChangeComparison {

  /**
   * Returns name of entity
   *
   * @return string
   */
  protected function getEntity() {
    return 'Case';
  }

  /**
   * Returns name of the field
   *
   * @return string
   */
  protected function getEntityStatusFieldName() {
    return 'status_id';
  }

  /**
   * Returns an array with all possible options for the field, in
   * case the field is a select field, e.g. gender, or financial type
   * Return false when the field is a select field
   *
   * This method could be overridden by child classes to return the option
   *
   * The return is an array with the field option value as key and the option label as value
   *
   * @return array
   */
  public function getFieldOptions() {
    return CRM_CivirulesConditions_Case_Status::getEntityStatusList(TRUE);
  }

  /**
   * Returns an array with all possible options for the field, in
   * case the field is a select field, e.g. gender, or financial type
   * Return false when the field is a select field
   *
   * This method could be overridden by child classes to return the option
   *
   * The return is an array with the field option value as key and the option
   * label as value
   *
   * @return array
   */
  public function getFieldOptionsNames() {
    $return = [];
    $params = [
      'return' => ["name", "value"],
      'option_group_id' =>  'case_status',
      'options' => ['limit' => 0, 'sort' => "label ASC"],
    ];
    try {
      $options = civicrm_api3('OptionValue', 'get', $params)['values'];
      foreach ($options as $option) {
        $return[$option['value']] = $option['name'];
      }
    } catch (CiviCRM_API3_Exception $ex) {}

    return $return;
  }

  /**
   * Returns true when the field is a select option with multiple select
   *
   * @see getFieldOptions
   * @return bool
   */
  public function isMultiple() {
    return true;
  }

}
