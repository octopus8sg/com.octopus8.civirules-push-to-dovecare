<?php

class CRM_CivirulesPostTrigger_MembershipPayment extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of additional entities provided in this trigger
   *
   * @return array of CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function getAdditionalEntities() {
    $entities = parent::getAdditionalEntities();
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Membership', 'Membership', 'CRM_Member_DAO_Membership' , 'Membership');
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Contact', 'Contact', 'CRM_Contact_DAO_Contact' , 'Contact');
    $entities[] = new CRM_Civirules_TriggerData_EntityDefinition('Contribution', 'Contribution', 'CRM_Contribute_DAO_Contribution' , 'Contribution');
    return $entities;
  }

  /**
   * Alter the trigger data with extra data
   *
   * @param \CRM_Civirules_TriggerData_TriggerData $triggerData
   */
  public function alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
    $membershipPayment = $triggerData->getEntityData('MembershipPayment');
    try {
      $membership = civicrm_api3('Membership', 'getsingle', ['id' => $membershipPayment['membership_id']]);
      $triggerData->setEntityData('Membership', $membership);
      $contact_id = $membership['contact_id'];
      $triggerData->setContactId($contact_id);
      $contact = civicrm_api3('Contact', 'getsingle', ['id' => $contact_id]);
      $triggerData->setEntityData('Contact', $contact);
      $contribution = civicrm_api3('Contribution', 'getsingle', ['id' => $membershipPayment['contribution_id']]);
      $triggerData->setEntityData('Contribution', $contribution);
    } catch (\Exception $e) {
      \Civi::log('civirules')->error('Error occurred loading additional entity data for membership payment trigger: ' . $e->getMessage());
    }

    parent::alterTriggerData($triggerData);
  }

}
