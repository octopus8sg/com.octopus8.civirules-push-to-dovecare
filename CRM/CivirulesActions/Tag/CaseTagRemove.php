<?php

class CRM_CivirulesActions_Tag_CaseTagRemove extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   * @throws
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $entityId = $triggerData->getEntityId();
    $actionParams = $this->getActionParameters();
    $entityTable = "civicrm_case";
    $api4 = CRM_Civirules_Utils::isApi4Active();
    if (isset($actionParams['tag_id'])) {
      foreach ($actionParams['tag_id'] as $tagId) {
        if ($api4) {
          CRM_CivirulesActions_Tag_EntityTag::deleteApi4EntityTag($entityTable, $entityId, $tagId);
        }
        else {
          CRM_CivirulesActions_Tag_EntityTag::deleteApi3EntityTag($entityTable, $entityId, $tagId);
        }
      }
    }
  }

  /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/tag/entitytag', 'tn=civicrm_case&rule_action_id='
      . $ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   * @throws
   */
  public function userFriendlyConditionParams() {
    $actionParams = $this->getActionParameters();
    $labels = [];
    $tableName = "civicrm_case";
    if (CRM_Civirules_Utils::isApi4Active()) {
      $tags = CRM_CivirulesActions_Tag_EntityTag::getApi4Tags($tableName);
    }
    else {
      $tags = CRM_CivirulesActions_Tag_EntityTag::getApi3Tags($tableName);
    }

    if (isset($actionParams['tag_id'])) {
      foreach ($actionParams['tag_id'] as $tagId) {
        if (isset($tags[$tagId])) {
          $labels[] = $tags[$tagId];
        }
      }
    }
    return "These tags will be removed from the case:  " . implode(", ", $labels);
  }

  /**
   * Validates whether this action works with the selected trigger.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    $entities = $trigger->getProvidedEntities();
    return isset($entities['Case']);
  }

  /**
   * Returns condition data as an array and ready for export.
   * E.g. replace ids for names.
   *
   * @return array
   */
  public function exportActionParameters() {
    $action_params = parent::exportActionParameters();
    foreach($action_params['tag_id'] as $i=>$j) {
      try {
        $action_params['tag_id'][$i] = civicrm_api3('Tag', 'getvalue', [
          'return' => 'name',
          'id' => $j,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    return $action_params;
  }

  /**
   * Returns condition data as an array and ready for import.
   * E.g. replace name for ids.
   *
   * @return string
   */
  public function importActionParameters($action_params = NULL) {
    foreach($action_params['tag_id'] as $i=>$j) {
      try {
        $action_params['tag_id'][$i] = civicrm_api3('Tag', 'getvalue', [
          'return' => 'id',
          'name' => $j,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    return parent::importActionParameters($action_params);
  }

}
