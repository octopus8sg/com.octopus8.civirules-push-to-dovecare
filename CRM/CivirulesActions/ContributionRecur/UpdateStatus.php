<?php
/**
 * @author Alain Benbassat (CiviCooP) <alain.benbassat@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_ContributionRecur_UpdateStatus extends CRM_CivirulesActions_Generic_Api {

  /**
   * Method to get the api entity to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected function getApiEntity() {
    return 'ContributionRecur';
  }

  /**
   * Method to get the api action to process in this CiviRule action
   *
   * @access protected
   * @abstract
   */
  protected function getApiAction() {
    return 'Update';
  }

  protected function getApiVersion(): int {
    return 4;
  }

  /**
   * Returns an array with parameters used for processing an action
   *
   * @param array $parameters
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   *
   * @return array
   */
  protected function alterApiParameters(array $parameters, CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    $contributionRecurData = $triggerData->getEntityData('ContributionRecur');
    if (empty($contributionRecurData)) {
      return $parameters;
    }
    // Reset the params - API4 does not accept invalid params
    $parameters = [];
    // Add the API4 params
    $parameters['where'][] = ['id', '=', $contributionRecurData['id']];
    $parameters['values']['contribution_status_id'] = $actionParams['status_id'];
    return $parameters;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * Return FALSE if you do not need extra data input
   *
   * @param int $ruleActionId
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/contributionrecur_update_status', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @throws \CiviCRM_API3_Exception
   */
  public function userFriendlyConditionParams() {
    $return = '';
    $params = $this->getActionParameters();
    $status = \Civi\Api4\OptionValue::get(FALSE)
      ->addSelect('value', 'label')
      ->addWhere('value', '=', $params['status_id'])
      ->addWhere('option_group_id:name', '=', 'contribution_recur_status')
      ->execute()
      ->first()['label'];
    $return .= ts("Set Status to: %1", [1 => $status]);
    return $return;
  }

  /**
   * This function validates whether this action works with the selected trigger.
   *
   * This function could be overridden in child classes to provide additional validation
   * whether an action is possible in the current setup.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   *
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    if (isset($entities['ContributionRecur'])) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $entity = $this->getApiEntity();
    $action = $this->getApiAction();

    $params = $this->getActionParameters();

    // alter parameters by subclass
    $params = $this->alterApiParameters($params, $triggerData);

    $contributionRecurData = $triggerData->getEntityData('ContributionRecur');
    if (empty($contributionRecurData['id'])) {
      $this->logAction('No Recur ID found to update status of ContributionRecur');
    }
    else {
      // execute the action
      $this->executeApiAction($entity, $action, $params);
    }
  }


}
