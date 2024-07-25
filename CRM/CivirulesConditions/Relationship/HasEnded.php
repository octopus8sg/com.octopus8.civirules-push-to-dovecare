<?php
/**
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */
class CRM_CivirulesConditions_Relationship_HasEnded extends CRM_Civirules_Condition {

  /**
   * Method to determine if the condition is valid
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $relationship = $triggerData->getEntityData('Relationship');
    // if is_active = FALSE then relationship ended
    if ($relationship['is_active'] == FALSE) {
      return TRUE;
    }
    else {
      // if end_date <= today then relationship ended
      if (isset($relationship['end_date']) && !empty($relationship['end_date'])) {
        if (!$relationship['end_date'] instanceof DateTime){
          $endDate = new DateTime($relationship['end_date']);
        }
        else {
          $endDate = $relationship['end_date'];
        }
        $nowDate = new DateTime();
        if ($endDate->format('YmdHis') <= $nowDate->format('YmdHis')) {
          return TRUE;
        }

      }
    }
    return FALSE;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return FALSE;
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether a condition is possible in the current setup. E.g. we could have a condition
   * which works on contribution or on contributionRecur then this function could do
   * this kind of validation and return false/true
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return $trigger->doesProvideEntity('Relationship');
  }

}
