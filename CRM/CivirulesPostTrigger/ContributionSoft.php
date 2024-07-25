<?php
/**
 * @author Jon Goldberg <jon@megaphonetech.com>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesPostTrigger_ContributionSoft extends CRM_Civirules_Trigger_Post {

  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID = NULL) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);
    $contributionSoft = $triggerData->getEntityData('ContributionSoft');
    $contribution = civicrm_api3('Contribution', 'getsingle', array('id' => $contributionSoft['contribution_id']));
    $triggerData->setEntityData('Contribution', $contribution);
    return $triggerData;
  }

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Contribution', 'Contribution', 'CRM_Contribute_DAO_Contribution', 'Contribution');
    return $entities;
  }
}
