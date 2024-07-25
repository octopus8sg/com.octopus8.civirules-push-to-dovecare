<?php
/**
 * Class to process action end or delete relationship
 *
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @date 26 Aug 2021
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

use Civi\Api4\Relationship;
use Civi\Api4\RelationshipType;
use CRM_Civirules_ExtensionUtil as E;

class CRM_CivirulesActions_Relationship_End extends CRM_Civirules_Action {

  /**
   * Method processAction to execute the action
   *
   * @param \CRM_Civirules_TriggerData_TriggerData $triggerData
   *
   * @return void
   * @throws \Exception
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $actionParams = $this->getActionParameters();
    $contactId = (int) $triggerData->getContactId();
    if (!empty($contactId) && isset($actionParams['operation'])) {
      if ($actionParams['operation'] == 1) {
        $this->deleteRelationship($actionParams, $contactId);
      }
      else {
        $this->disableRelationship($actionParams, $contactId);
      }
    }
  }

  /**
   * Method to delete the relationships
   *
   * @param array $actionParams
   * @param int $contactId
   *
   * @return void
   */
  private function deleteRelationship(array $actionParams, int $contactId) {
    try {
      Relationship::delete(FALSE)
        ->addClause('OR', ['contact_id_a', '=', $contactId], ['contact_id_b', '=', $contactId])
        ->addWhere('relationship_type_id', '=', (int) $actionParams['relationship_type_id'])
        ->execute();
    }
    catch (\Exception $ex) {
      Civi::log()->error(E::ts("Could not delete relationships with CiviRules in ") . __METHOD__ . ", error from API4 Relationship delete: ". $ex->getMessage());
    }
  }

  /**
   * Method to disable the relationships
   *
   * @param array $actionParams
   * @param int $contactId
   *
   * @throws \Exception
   */
  private function disableRelationship(array $actionParams, int $contactId) {
    if (isset($actionParams['end_date']) && !empty($actionParams['end_date'])) {
      $endDate = new DateTime($actionParams['end_date']);
    }
    else {
      $endDate = new DateTime();
    }
    try {
      Relationship::update(FALSE)
        ->addValue('end_date', $endDate->format('Y-m-d'))
        ->addValue('is_active', FALSE)
        ->addWhere('relationship_type_id', '=', (int) $actionParams['relationship_type_id'])
        ->addClause('OR', ['contact_id_a', '=', $contactId], ['contact_id_b', '=', $contactId])
        ->execute();
    }
    catch (\Exception $ex) {
      Civi::log()->error(E::ts("Could not disable relationships with CiviRules in ") . __METHOD__ . ", error from API4 Relationship update: ". $ex->getMessage());
    }
  }

  /**
   * Method to add url for form action for rule
   *
   * @param int $ruleActionId
   *
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/civirule/form/action/relationship/end', 'rule_action_id=' . $ruleActionId);
  }

  /**
   * Method to create a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  public function userFriendlyConditionParams() {
    $actionParams = $this->getActionParameters();
    if ($actionParams['operation'] == 1) {
      $label = "Delete relationship(s) for contact of type ";
    }
    else {
      $label = "Disable relationship(s) for contact of type ";
    }
    $label .= $this->getRelationshipTypeLabel($actionParams['relationship_type_id']);
    if (isset($actionParams['end_date']) && !empty($actionParams['end_date']) && $actionParams['operation'] != 1) {
      $endDate = new DateTime($actionParams['end_date']);
      $label .= " on end date " . $endDate->format("d-m-Y");
    }
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
    try {
      $action_params['relationship_type_id'] = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'name_a_b',
        'id' => $action_params['relationship_type_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
    }
    return $action_params;
  }

  /**
   * Returns condition data as an array and ready for import.
   * E.g. replace name for ids.
   *
   * @param array|NULL $action_params
   *
   * @return string
   */
  public function importActionParameters($action_params = NULL) {
    try {
      $action_params['relationship_type_id'] = civicrm_api3('RelationshipType', 'getvalue', [
        'return' => 'id',
        'name_a_b' => $action_params['relationship_type_id'],
      ]);
    } catch (CiviCRM_API3_Exception $e) {
    }
    return parent::importActionParameters($action_params);
  }

  /**
   * Method to get the relationship type label based on contact_a or contact_b
   *
   * @param int $relationshipTypeId
   *
   * @return mixed|string
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function getRelationshipTypeLabel(int $relationshipTypeId) {
    $relationshipType = RelationshipType::get()
      ->addSelect('label_a_b')
      ->addWhere('id', '=', $relationshipTypeId)
      ->setLimit(1)
      ->execute();
    $label = $relationshipType->first();
    return $label['label_a_b'];
  }

}
