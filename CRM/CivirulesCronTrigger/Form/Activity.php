<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use CRM_Civirules_ExtensionUtil as E;

abstract class CRM_CivirulesCronTrigger_Form_Activity extends CRM_CivirulesTrigger_Form_Form {

  protected function getActivityType() {
    return CRM_Civirules_Utils::getActivityTypeList();
  }

  protected function getActivityStatus() {
    $activityStatusList = [];
    $activityStatusOptionGroupId = CRM_Civirules_Utils::getOptionGroupIdWithName('activity_status');
    $params = [
      'option_group_id' => $activityStatusOptionGroupId,
      'is_active' => 1,
      'options' => ['limit' => 0]
    ];
    $activityStatuses = civicrm_api3('OptionValue', 'Get', $params);
    foreach ($activityStatuses['values'] as $optionValue) {
      $activityStatusList[$optionValue['value']] = $optionValue['label'];
    }
    return $activityStatusList;
  }

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');

    $result = civicrm_api3('ActivityContact', 'getoptions', [
      'field' => "record_type_id",
    ]);
    $options[0] = E::ts('All contacts');
    $options = $options + $result['values'];

    $this->add('select', 'record_type', E::ts('Trigger for'), $options, TRUE, ['class' => 'crm-select2 huge']);
    $this->addYesNo('case_activity', ts('Trigger for case activities?'), FALSE, TRUE);

    $this->addButtons([
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => ts('Cancel')]
    ]);
  }

}
