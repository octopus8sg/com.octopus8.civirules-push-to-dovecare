<?php

class CRM_CivirulesActions_Activity_ChangeStatus extends CRM_Civirules_Action {

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        $contactId = $triggerData->getContactId();
        //$objectId = $triggerData->getEntityData('Activity');

        //Civi::log()->debug("HERE". $objectId);

        $eventactivities = civicrm_api4('Activity', 'get', [//id i update, then i can get the activity id then i can 
            'select' => [
              'id',
              'Event_Signups.Event_activities',
            ],
            'where' => [
              ['activity_type_id', '=', 69],
              ['status_id', '=', 9],//sign up
              //['assignee_contact_id', '=', $contactId],
            ],
            'orderBy' => [
              'modified_date' => 'DESC',
            ],
            'limit' => 1,
            'checkPermissions' => TRUE,
        ]);

        // Fetch the first item from the event activities array
        $recentActivity = $eventactivities[0]; // Since it's the first record based on your limit
        
        Civi::log()->debug("Most recent activity: " . json_encode($recentActivity));

        // Correctly assign variables from the recent activity
        $title = $recentActivity['subject'];
        $address = $recentActivity['Event_Field.address'];
        $activityID = $recentActivity['id'];
        $groupAAP = $recentActivity['Event_Field.Group_AAP_is_catered_to'];
        $maxParticipants = $recentActivity['Event_Field.Maximum_number_of_participants'];
        $formattedDate = date('d/m/Y', strtotime($recentActivity['activity_date_time']));
        $newTitle = $title . '@' . $formattedDate;
        Civi::log()->debug($newTitle);

    }

    public function getExtraDataInputUrl($ruleActionId) {
        return FALSE;
    }

    public function getLabel() {
        return ts('Recommended AAP');
    }

    public function getDescription() {
        return ts('This action will recommend the AAP to contacts in the same group.');
    }
}
?>