<?php

/**
 * Class for CiviRules Group Contact add action.
 *
 * Adds a user to a group
 *
 * @author Jaap Jansma (CiviCooP) <jaap.jansma@civicoop.org>
 * @license AGPL-3.0
 */

use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Tag_Sync extends CRM_Civirules_Action {

  /**
   * Process the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   *
   * @throws Exception
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $action_params = $this->getActionParameters();
    $tag_ids = $action_params['tag_ids'];
    $target_contacts = $this->getTargetContacts($triggerData);
    $type = $action_params['type'];
    $selected_tags = $this->getSelectedTagsOnContact($triggerData->getContactId(), $tag_ids);
    foreach($target_contacts as $target_contact_id) {
      $target_selected_tags = $this->getSelectedTagsOnContact($target_contact_id, $tag_ids);
      foreach($tag_ids as $tag_id) {
        if (in_array($tag_id, $selected_tags) && !in_array($tag_id, $target_selected_tags)) {
          civicrm_api3('EntityTag', 'create', [
            'entity_table' => 'civicrm_contact',
            'entity_id' => $target_contact_id,
            'tag_id' => $tag_id,
          ]);
        } elseif (!in_array($tag_id, $selected_tags) && in_array($tag_id, $target_selected_tags) && $type == 'sync') {
          civicrm_api3('EntityTag', 'delete', ['tag_id' => $tag_id, 'contact_id' => $target_contact_id]);
        }
      }
    }
  }

  protected function getSelectedTagsOnContact($contact_id, $tag_ids_to_check) {
    $return = [];
    $strImplodedTagsToCheck = implode(", ", $tag_ids_to_check);
    $sql = "SELECT `id`, `tag_id` FROM `civicrm_entity_tag` WHERE `tag_id` IN ({$strImplodedTagsToCheck}) AND `entity_id` = %1 AND `entity_table` = 'civicrm_contact'";
    $sqlParams[1] = [$contact_id, 'Integer'];
    $dao = \CRM_Core_DAO::executeQuery($sql, $sqlParams);
    while ($dao->fetch()) {
      $return[$dao->id] = $dao->tag_id;
    }
    return $return;
  }

  protected function getTargetContacts(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $return = [];
    try {
      $actionParams = $this->getActionParameters();
      foreach($actionParams['rel_type_ids'] as $rel_type_id) {
        $params['relationship_type_id'] = substr($rel_type_id, 4);
        $params['is_active'] = '1';
        $params['options']['limit'] = '0';
        if (strpos($rel_type_id, 'a_b_') === 0) {
          $params['contact_id_a'] = $triggerData->getContactId();
          $return_field = 'contact_id_b';
        }
        else {
          $params['contact_id_b'] = $triggerData->getContactId();
          $return_field = 'contact_id_a';
        }
        $apiReturn = civicrm_api3('Relationship', 'get', $params);
        foreach ($apiReturn['values'] as $value) {
          $return[] = $value[$return_field];
        }
      }
    } catch (\Exception $ex) {
      // Do nothing
    }
    return $return;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a
   * action
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleActionId
   *
   * @return bool|string
   * $access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/sync_tag', 'rule_action_id=' . $ruleActionId);
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
    $tags = '';
    $relationshipTypes = '';
    foreach($params['tag_ids'] as $tag_id) {
      $tag = civicrm_api3('Tag', 'getvalue', ['return' => 'name', 'id' => $tag_id]);
      if (strlen($tags)) {
        $tags .= ', ';
      }
      $tags .= $tag;
    }
    $relationshipTypeOptions = CRM_Civirules_Utils::getRelationshipTypes();
    foreach($params['rel_type_ids'] as $rel_type_id) {
      if (strlen($relationshipTypes)) {
        $relationshipTypes .= ', ';
      }
      $relationshipTypes .= $relationshipTypeOptions[$rel_type_id];
    }

    if ($params['type'] == 'sync') {
      return E::ts('Sync tags: %1 to related contacts with relationship type %2', [
        1 => $tags,
        2 => $relationshipTypes
      ]);
    } else {
      return E::ts('Copy tags: %1 to related contacts with relationship type %2', [
        1 => $tags,
        2 => $relationshipTypes
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
    foreach($action_params['tag_ids'] as $i=>$j) {
      try {
        $action_params['tag_ids'][$i] = civicrm_api3('Tag', 'getvalue', [
          'return' => 'name',
          'id' => $j,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    foreach($action_params['rel_type_ids'] as $i=>$j) {
      $rel_dir = substr($j, 0, 4);
      $rel_type = substr($j, 4);
      try {
        $action_params['rel_type_ids'][$i] = $rel_dir . civicrm_api3('Tag', 'getvalue', [
          'return' => 'name_a_b',
          'id' => $rel_type,
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
    foreach($action_params['tag_ids'] as $i=>$j) {
      try {
        $action_params['tag_ids'][$i] = civicrm_api3('Tag', 'getvalue', [
          'return' => 'id',
          'name' => $j,
        ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    foreach($action_params['rel_type_ids'] as $i=>$j) {
      $rel_dir = substr($j, 0, 4);
      $rel_type = substr($j, 4);
      try {
        $action_params['rel_type_ids'][$i] = $rel_dir . civicrm_api3('Tag', 'getvalue', [
            'return' => 'id',
            'name_a_b' => $rel_type,
          ]);
      } catch (CiviCRM_API3_Exception $e) {
      }
    }
    return parent::importActionParameters($action_params);
  }


}
