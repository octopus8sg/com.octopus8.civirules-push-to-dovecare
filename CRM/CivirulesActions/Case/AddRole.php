<?php

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Case_AddRole extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $case = $triggerData->getEntityData("Case");
    $params = $this->getActionParameters();
    $api_params['contact_id_a'] = $triggerData->getContactId();
    $api_params['contact_id_b'] = $params['cid'];
    $api_params['relationship_type_id'] = $params['role'];
    $api_params['case_id'] = $case['id'];
    try {
      civicrm_api3('Relationship', 'create', $api_params);
    } catch(\Exception $ex) {
      // Do nothing
    }
  }

  /**
   * Returns condition data as an array and ready for export.
   * E.g. replace ids for names.
   *
   * @return array
   */
  public function exportActionParameters() {
    $action_params = parent::exportActionParameters();
    try {
      $action_params['role'] = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'name_a_b',
        'id' => $action_params['role'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
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
    try {
      $action_params['role'] = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'id',
        'name_a_b' => $action_params['role'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
    }
    return parent::importActionParameters($action_params);
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a action
   *
   * @param int $ruleActionId
   * @return bool|string
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/case/addrole', 'rule_action_id='.$ruleActionId);
  }


  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $roles = self::getCaseRoles();
    $contactDisplayName = \Civi\Api4\Contact::get(FALSE)
      ->addWhere('id', '=', $params['cid'])
      ->execute()
      ->first()['display_name'] ?? '';
    return E::ts('Add %2 to the case with role <em>%1</em>', [1 => $roles[$params['role']], 2 => $contactDisplayName]);
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
   * Returns a list of possible case roles
   *
   * @return array
   * @throws \CiviCRM_API3_Exception
   */
  public static function getCaseRoles() {
    $relationshipTypesApi = civicrm_api3('RelationshipType', 'get', ['options' => ['limit' => 0]]);
    $caseRoles = [];
    foreach($relationshipTypesApi['values'] as $relType) {
      $caseRoles[$relType['id']] = $relType['label_a_b'];
    }
    return $caseRoles;
  }
}
