<?php

use Civi\Api4\Contribution;
use Civi\Api4\ContributionRecur;
use Civi\Api4\LineItem;

/**
 * Class for CiviRules post trigger handling - Membership
 *
 * @license AGPL-3.0
 */

class CRM_CivirulesPostTrigger_Membership extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), 'Membership');
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Member_DAO_Membership';
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Contribution', 'Contribution', 'CRM_Contribute_DAO_Contribute' , 'Contribution');
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('ContributionRecur', 'ContributionRecur', 'CRM_Contribute_DAO_ContributionRecur' , 'ContributionRecur');
    return $entities;
  }

  /**
   * Override alter trigger data.
   *
   * Add data for contribution if available
   */
  public function alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
    try {
      $membership = $triggerData->getEntityData('Membership');
      if (!empty($membership['contribution_recur_id'])) {
        $recur = ContributionRecur::get(FALSE)
          ->addWhere('id', '=', $membership['contribution_recur_id'])
          ->execute()
          ->first();
        if (!empty($recur)) {
          $triggerData->setEntityData('ContributionRecur', $recur);
        }
      }
      $membershipID = $triggerData->getEntityId();
      // Retrieve the membership entity
      $lineItem = LineItem::get(FALSE)
        ->addWhere('entity_table:name', '=', 'civicrm_membership')
        ->addWhere('entity_id', '=', $membershipID)
        ->addOrderBy('id', 'DESC')
        ->execute()
        ->first();
      if (!empty($lineItem['contribution_id'])) {
        $contribution = Contribution::get(FALSE)
          ->addWhere('id', '=', $lineItem['contribution_id'])
          ->execute()
          ->first();
        $triggerData->setEntityData('Contribution', $contribution);
      }
    } catch (Exception $e) {
      // Do nothing. There could be an exception when the contribution does not exists in the database anymore.
      \Civi::log('civirules')->error('Error occurred loading contribution for membership: ' . $e->getMessage());
    }

    parent::alterTriggerData($triggerData);
  }

}
