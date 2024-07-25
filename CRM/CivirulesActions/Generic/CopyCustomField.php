<?php
/**
 * Class for CiviRules Copy Custom Field
 *
 * @author Björn Endres (SYSTOPIA) <endres@systopia.de>
 * @license AGPL-3.0
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Generic_CopyCustomField extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_params = $this->getActionParameters();

    // Source field
    $copy_from_field_id = $action_params['copy_from_field_id'];

    // Get the entity the custom field extends.
    $from_entity = civicrm_api3('CustomField', 'getsingle', [
      'return' => ['custom_group_id.extends'],
      'id' => $copy_from_field_id,
    ])['custom_group_id.extends'];
    if (in_array($from_entity, ['Individual', 'Organization', 'Household'])) {
      $from_entity = 'Contact';
    }

    // Get the ID of the entity we're updating.
    if (empty($entityData = $triggerData->getEntityData($from_entity))) {
      throw new Exception("Custom field id $copy_from_field_id is not compatible "
        . "with the entity this rule was triggered for");
    }

    $fromEntityId = $entityData['id'];

    // Target field
    $field_id = $action_params['field_id'];

    // Get the entity the custom field extends.
    $entity = civicrm_api3('CustomField', 'getsingle', [
      'return' => ['custom_group_id.extends'],
      'id' => $field_id,
    ])['custom_group_id.extends'];
    if (in_array($entity, ['Individual', 'Organization', 'Household'])) {
      $entity = 'Contact';
    }

    // Get the ID of the entity we're updating.
    if (empty($entityData = $triggerData->getEntityData($entity))) {
      throw new Exception("Custom field id $field_id is not compatible "
        . "with the entity this rule was triggered for");
    }

    $entityId = $entityData['id'];

    // Get new value
    $new_value = "";
    try {
      $new_value = civicrm_api3($from_entity, 'getvalue', ['id' => $fromEntityId, 'return' => 'custom_' . $copy_from_field_id]);
    }
    catch (\CiviCRM_API3_Exception $ex) {
      // Do nothing.
    }

    // Ensure the new value isn't the same, to prevent unnecessary writes and avoid infinite loops.
    $existingRecord = civicrm_api3($entity, 'get', [
      'id'                 => $entityId,
      "custom_{$field_id}" => $new_value,
    ]);
    if (!$existingRecord['count']) {
      // set the new value using the API
      civicrm_api3($entity, 'create', [
        'id'                 => $entityId,
        "custom_{$field_id}" => $new_value,
      ]);
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
    if (!empty($action_params['field_id'])) {
      try {
        $customField = civicrm_api3('CustomField', 'getsingle', [
          'id' => $action_params['field_id'],
        ]);
        $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
          'id' => $customField['custom_group_id'],
        ]);
        unset($action_params['field_id']);
        $action_params['custom_group'] = $customGroup['name'];
        $action_params['custom_field'] = $customField['name'];
      } catch (\CiviCRM_Api3_Exception $e) {
        // Do nothing.
      }
    }
    if (!empty($action_params['copy_from_field_id'])) {
      try {
        $customField = civicrm_api3('CustomField', 'getsingle', [
          'id' => $action_params['copy_from_field_id'],
        ]);
        $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
          'id' => $customField['custom_group_id'],
        ]);
        unset($action_params['copy_from_field_id']);
        $action_params['copy_from_custom_group'] = $customGroup['name'];
        $action_params['copy_from_custom_field'] = $customField['name'];
      } catch (\CiviCRM_Api3_Exception $e) {
        // Do nothing.
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
    if (!empty($action_params['custom_group'])) {
      try {
        $customField = civicrm_api3('CustomField', 'getsingle', [
          'name' => $action_params['custom_field'],
          'custom_group_id' => $action_params['custom_group'],
        ]);
        $action_params['field_id'] = $customField['id'];
        unset($action_params['custom_group']);
        unset($action_params['custom_field']);
      } catch (\CiviCRM_Api3_Exception $e) {
        // Do nothing.
      }
    }
    if (!empty($action_params['copy_from_custom_group'])) {
      try {
        $customField = civicrm_api3('CustomField', 'getsingle', [
          'name' => $action_params['copy_from_custom_field'],
          'custom_group_id' => $action_params['copy_from_custom_group'],
        ]);
        $action_params['copy_from_field_id'] = $customField['id'];
        unset($action_params['copy_from_custom_group']);
        unset($action_params['copy_from_custom_field']);
      } catch (\CiviCRM_Api3_Exception $e) {
        // Do nothing.
      }
    }
    return parent::importActionParameters($action_params);
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
    return CRM_Utils_System::url('civicrm/civirule/form/action/generic/copycustomvalue', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * This function validates whether this action works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether an action is possible in the current setup.
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return TRUE;
  }
}
