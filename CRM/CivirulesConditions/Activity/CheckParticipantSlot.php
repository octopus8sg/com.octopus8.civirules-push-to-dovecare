<?php

class CRM_CivirulesConditions_Activity_CheckParticipantSlot extends CRM_Civirules_Condition {

    /**
     * Checks if the condition is valid by comparing two custom fields.
     *
     * @param CRM_Civirules_TriggerData_TriggerData $triggerData
     * @return bool
     * @access public
     */
    public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        //Civi::log()->debug("CheckParticipantSlot condition triggered.");

        // Get the activity ID from the trigger data
        $activityId = $triggerData->getEntityId();
        //Civi::log()->debug("Activity ID retrieved: $activityId");

        // Fetch the custom field values for the activity using civicrm_api4
        try {
            Civi::log()->debug("Attempting to retrieve custom fields for activity ID: $activityId");

            $activities = civicrm_api4('Activity', 'get', [
                'select' => [
                    'Event_Sign_up.Attended',
                ],
                'where' => [
                    ['id', '=', $activityId],
                ],
                'limit' => 1,
                'checkPermissions' => TRUE,
            ]);

        } catch (Exception $e) {
            Civi::log()->error("API Error while fetching activity data: " . $e->getMessage());
            return false;
        }

       // Get the values of the custom fields
       $activity = $activities[0];
       $attendedStatus = $activity['Event_Sign_up.Attended'] ?? null;

       // Check if the two values are the same (i.e., when the rule should trigger)
       if ($attendedStatus == 2) {
           return true; // Trigger the action when the values are the same
       }
       return false; // Skip the rule if the values are different
   }
    /**
     * Returns a redirect URL to extra data input from the user after adding a condition.
     * This condition does not need extra data input, so return false.
     *
     * @param int $ruleConditionId
     * @return bool|string
     * @access public
     */
    public function getExtraDataInputUrl($ruleConditionId) {
        Civi::log()->debug("getExtraDataInputUrl called but returning false as no extra input is needed.");
        return false; // No additional input needed for this condition
    }

    /**
     * Specifies the entity that this condition works with.
     * In this case, the condition works with the 'Activity' entity.
     *
     * @return array
     * @access public
     */
    public function requiredEntities() {
        return ['Activity'];
    }

    /**
     * Return the label for this condition.
     *
     * @return string
     * @access public
     */
    public function getLabel() {
        return ts('Check if Maximum Participants and Remaining Slots are the Same');
    }

    /**
     * Return the description for this condition.
     *
     * @return string
     * @access public
     */
    public function getDescription() {
        return ts('This condition checks if the Maximum Participants and Remaining Slots are the same.');
    }
}