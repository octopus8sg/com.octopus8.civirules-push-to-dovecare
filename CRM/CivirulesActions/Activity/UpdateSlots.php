<?php

class CRM_CivirulesActions_Activity_UpdateSlots extends CRM_Civirules_Action {

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        $activityId = $triggerData->getEntityId();

        $activities = civicrm_api4('Activity', 'get', [
            'select' => [
              'Event_Sign_up.Event_Activity',
              'Event_Sign_up.Session_Id',
            ],
            'where' => [
              ['id', '=', $activityId],
            ],
            'limit' => 1,
            'checkPermissions' => TRUE,
          ]);

        $recentActivity = $activities[0];

        Civi::log()->debug("Most recent activity: " . json_encode($recentActivity));

        $eventid = $recentActivity['Event_Sign_up.Event_Activity'];
        $sessionid = $recentActivity['Event_Sign_up.Session_Id'];

        $eventactivities = civicrm_api4('Activity', 'get', [
          'select' => [
            'AAP_Session_Details.Remaining_Capacity',
          ],
          'where' => [
            ['id', '=', $sessionid],
          ],
          'limit' => 1,
          'checkPermissions' => TRUE,
        ]);

        $recentActivity2 = $eventactivities[0];
        $remainingSlots = $recentActivity2['AAP_Session_Details.Remaining_Capacity'];
        $newRemainingSlots = $remainingSlots - 1;

        Civi::log()->debug("Remaining Slots after decrement: " . $newRemainingSlots);

        $results1 = civicrm_api4('Activity', 'update', [
            'values' => [
                'AAP_Session_Details.Remaining_Capacity' => $newRemainingSlots,
            ],
            'where' => [
                ['id', '=', $sessionid],
            ],
            'checkPermissions' => TRUE,
        ]);

        // Update attended status
        // $results2 = civicrm_api4('Activity', 'update', [
        //     'values' => [
        //         'Event_Sign_up.Attended' => 2,
        //     ],
        //     'where' => [
        //         ['id', '=', $activityId],
        //     ],
        //     'checkPermissions' => TRUE,
        // ]);
        
    }

public function getExtraDataInputUrl($ruleActionId) {
    return FALSE;
}

public function getLabel() {
    return ts('Update Fields in AAP');
}

public function getDescription() {
    return ts('This action will update the remaining slots and title in the AAP.');
}
}
?>