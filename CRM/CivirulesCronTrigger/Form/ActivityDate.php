<?php
/**
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesCronTrigger_Form_ActivityDate extends CRM_CivirulesCronTrigger_Form_Activity {

  /**
   * Overridden parent method to build form
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    $this->add('select', 'activity_type_id', ts('Activity Type'), $this->getActivityType(), TRUE);
    $this->add('select', 'activity_status_id', ts('Activity Status'), $this->getActivityStatus(), TRUE);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->rule->trigger_params);
    if (!empty($data['activity_type_id'])) {
      $defaultValues['activity_type_id'] = $data['activity_type_id'];
    }
    if (!empty($data['activity_status_id'])) {
      $defaultValues['activity_status_id'] = $data['activity_status_id'];
    }

    if (!empty($data['record_type'])) {
      $defaultValues['record_type'] = $data['record_type'];
    } else {
      $defaultValues['record_type'] = 3; // Default to only targets
    }

    $defaultValues['case_activity'] = $data['case_activity'] ?? FALSE;

    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   */
  public function postProcess() {
    $data['activity_type_id'] = $this->_submitValues['activity_type_id'];
    $data['activity_status_id'] = $this->_submitValues['activity_status_id'];
    $data['record_type'] = $this->_submitValues['record_type'];
    $data['case_activity'] = $this->_submitValues['case_activity'];
    $this->rule->trigger_params = serialize($data);
    $this->rule->save();

    parent::postProcess();
  }

  /**
   * Returns a help text for this trigger.
   * The help text is shown to the administrator who is configuring the condition.
   *
   * @return string
   */
  protected function getHelpText() {
    return E::ts('Trigger rule when scheduled date for activity with status and type is reached.')
      . '<br/>'
      . E::ts('If "Trigger for case activities" is "Yes" then this will only trigger for case activities. If it is "No" then it will only trigger for activities that are not linked to a case.');
  }

}
