<?php
/**
 * Class for CiviRules post trigger handling - Membership Renewed
 *
 * @license AGPL-3.0
 */

class CRM_CivirulesPostTrigger_MembershipRenewed extends CRM_CivirulesPostTrigger_Membership {

  /**
   * Trigger a rule for this trigger
   *
   * @param string $op
   * @param string $objectName
   * @param int $objectId
   * @param object $objectRef
   * @param string $eventID
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID) {
    $triggerData = $this->getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);
    $membership = $triggerData->getEntityData('Membership');
    $originalMembership = $triggerData->getOriginalData();

    // Check if the Membership has been renewed (end_date has been increased by one membership term)
    // As a membership runs from [date] to [date - 1 day] we need to check if the new end_date matches the
    //   calculated end_date based on the original end_date + 1 day.
    $startDate = date('Y-m-d', strtotime("{$originalMembership['end_date']} + 1 day"));
    $membershipDates = CRM_Member_BAO_MembershipType::getDatesForMembershipType(
      $membership['membership_type_id'], $membership['join_date'], $startDate);
    if ($membershipDates['end_date'] !== CRM_Utils_Date::isoToMysql($membership['end_date'])) {
      if ($this->getRuleDebugEnabled()) {
        \Civi::log('civirules')->debug('CiviRules Trigger MembershipRenewed: NOT TRIGGERING. Calculated end_date: ' . $membershipDates['end_date'] . ' does not match actual end date: ' . CRM_Utils_Date::isoToMysql($membership['end_date']));
      }
      return;
    }

    $this->setTriggerData($triggerData);
    parent::triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID);
  }

}
