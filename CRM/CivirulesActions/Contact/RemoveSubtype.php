<?php

class CRM_CivirulesActions_Contact_RemoveSubtype extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $contactId = $triggerData->getContactId();

    $subTypes = CRM_Contact_BAO_Contact::getContactSubType($contactId);
    $contactType = CRM_Contact_BAO_Contact::getContactType($contactId);
    $typesToRemove = [];
    $changed = false;
    $actionParams = $this->getActionParameters();
    foreach($actionParams['sub_type'] as $subType) {
      if (in_array($subType, $subTypes )) {
        $typesToRemove[] = $subType;
        $changed = true;
      }
    }
    if ($changed) {
      $params['id'] = $contactId;
      $params['contact_id'] = $contactId;
      $params['contact_type'] = $contactType;
      $params['contact_sub_type'] = array_diff($subTypes, $typesToRemove);
      CRM_Contact_BAO_Contact::writeRecord($params);
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/contact/subtype/remove', 'rule_action_id='.$ruleActionId);
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $params = $this->getActionParameters();
    $label = ts('Remove contact subtype');
    $subTypeLabels = [];
    $subTypes = CRM_Contact_BAO_ContactType::contactTypeInfo();
    foreach($params['sub_type'] as $subType) {
      $subTypeLabels[] = $subTypes[$subType]['parent_label'].' - '.$subTypes[$subType]['label'];
    }
    $label .= ': ' . implode(', ', $subTypeLabels);
    return $label;
  }

  /**
   * Returns condition data as an array and ready for export.
   * E.g. replace ids for names.
   *
   * @return array
   */
  public function exportActionParameters() {
    $action_params = parent::exportActionParameters();
    foreach($action_params['sub_type'] as $i=>$j) {
      try {
        $action_params['sub_type'][$i] = civicrm_api3('ContactType', 'getvalue', [
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
    foreach($action_params['sub_type'] as $i=>$j) {
      try {
        $action_params['sub_type'][$i] = civicrm_api3('ContactType', 'getvalue', [
          'return' => 'id',
          'name' => $j,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    return parent::importActionParameters($action_params);
  }

}
