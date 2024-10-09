<?php

// class CRM_CivirulesActions_Activity_RecommendedAAP extends CRM_Civirules_Action {

//     public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
//         $contactId = $triggerData->getContactId();
//         $activityId = $triggerData->getEntityId();
     
//         $eventactivities = civicrm_api4('Activity', 'get', [
//             'select' => [
//               'id',
//               'subject',
//               'activity_date_time',
//               'Event_Creation.Target_Attendees',
//             ],
//            	'where' => [
//     			['id', '=', $activityId],
// 			],
//             'orderBy' => [
//               'modified_date' => 'DESC',
//             ],
//             'limit' => 1,
//             'checkPermissions' => TRUE,
//         ]);

//         // Fetch the first item from the event activities array
//         $recentActivity = $eventactivities[0];
        
//         Civi::log()->debug("Most recent activity: " . json_encode($recentActivity));

//         // Correctly assign variables from the recent activity
//         $title = $recentActivity['subject'];
//         $activityID = $recentActivity['id'];
//         $groupAAP = $recentActivity['Event_Creation.Target_Attendees'];
       

//         $SlotActivities = civicrm_api4('Activity', 'get', [//do this first
//             'select' => [
//               'AAP_Session_Details.Maximum_Capacity',
//               'AAP_Session_Details.Remaining_Capacity',
//               'activity_date_time',//fetch the parent activity id
//             ],
//             'where' => [
//               ['AAP_Session_Details.AAP_parent', '=', $activityId],
//               ['AAP_Session_Details.Remaining_Capacity', '!=', 0],
//             ],
//             'orderBy' => [
//               'activity_date_time' => 'ASC',
//             ],
//             'limit' => 25,
//             'checkPermissions' => TRUE,
//           ]);

//         // Fetch the first item from the event activities array
//         $recentActivity2 = $SlotActivities[0];
//         $id = $recentActivity2['id'];
//         $MaxCapacity = $recentActivity2['AAP_Session_Details.Maximum_Capacity'];   
//         $RemainingCapacity = $recentActivity2['AAP_Session_Details.Remaining_Capacity'];
//         $formattedDate2 = date('Y-m-d', strtotime($recentActivity2['activity_date_time']));
//         $newTitle = $title . '@' . $formattedDate2;


//         // Where condition based on $groupAAP
//         if ($groupAAP == 1) {
//             $wherestatement = ['group_id:label', '=', 'AAP (Robust)'];
//         } elseif ($groupAAP == 2) {
//             $wherestatement = ['group_id:label', '=', 'AAP (Frail)'];
//         } elseif ($groupAAP == 3) {
//             $wherestatement = [
//                 'OR', 
//                 [
//                     ['group_id:label', '=', 'AAP (Robust)'], 
//                     ['group_id:label', '=', 'AAP (Frail)']
//                 ]
//             ];
//         }        
       
//         // Fetch Group Contacts
//         $groupContacts = civicrm_api4('GroupContact', 'get', [
//             'select' => [
//               'contact_id',
//             ],
//             'where' => [
//               $wherestatement,
//             ],
//             'checkPermissions' => TRUE,
//         ]);

//         // Iterate through each group contact to log and create activities
//         foreach ($groupContacts as $groupContact) {
//             if (isset($groupContact['contact_id'])) {
//                 $contactId = $groupContact['contact_id'];
//                 Civi::log()->debug("Processing contact ID: " . $contactId);

//                 // Create an activity for each contact
//                 $results = civicrm_api4('Activity', 'create', [
//                     'values' => [
//                         'subject' => $newTitle,
//                         'source_contact_id' => $contactId,
//                         'activity_type_id' => 69,
//                         'status_id' => 16,
//                         'Event_Sign_up.Event_Activity' => $activityID,
//                         'Event_Sign_up.Session_Id' => $id,
//                         'Event_Sign_up.Number_of_Participants' => 1,
//                         'Event_Signups.Attended'=> 2,
//                     ],
//                     'checkPermissions' => TRUE,
//                 ]);
//             } else {
//                 Civi::log()->error("Missing contact_id in groupContact: " . json_encode($groupContact));
//             }
//         }
//     }

//     public function getExtraDataInputUrl($ruleActionId) {
//         return FALSE;
//     }

//     public function getLabel() {
//         return ts('Recommended AAP');
//     }

//     public function getDescription() {
//         return ts('This action will recommend the AAP to contacts in the same group.');
//     }
// }

?>
<?php

class CRM_CivirulesActions_Activity_RecommendedAAP extends CRM_Civirules_Action {

    public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
        $contactId = $triggerData->getContactId();
        $activityId = $triggerData->getEntityId();


        $SlotActivities = civicrm_api4('Activity', 'get', [
            'select' => [
              'AAP_Session_Details.AAP_parent',
              'activity_date_time',
            ],
            'where' => [
              ['id', '=', $activityId],
            ],
            'orderBy' => [
              'activity_date_time' => 'ASC',
            ],
            'limit' => 25,
            'checkPermissions' => TRUE,
          ]);

        // Fetch the first item from the event activities array
        $recentActivity2 = $SlotActivities[0];
        $parentId = $recentActivity2['AAP_Session_Details.AAP_parent'];
        $formattedDate2 = date('Y-m-d', strtotime($recentActivity2['activity_date_time']));


        $eventactivities = civicrm_api4('Activity', 'get', [
            'select' => [
              'subject',
              'Event_Creation.Target_Attendees',
            ],
           	'where' => [
    			['id', '=', $parentId],
			],
            'orderBy' => [
              'modified_date' => 'DESC',
            ],
            'limit' => 1,
            'checkPermissions' => TRUE,
        ]);

        // Fetch the first item from the event activities array
        $recentActivity = $eventactivities[0];
        
        Civi::log()->debug("Most recent activity: " . json_encode($recentActivity));

        $title = $recentActivity['subject'];
        $groupAAP = $recentActivity['Event_Creation.Target_Attendees'];
        $newTitle = $title . '@' . $formattedDate2;

        // Where condition based on $groupAAP
        if ($groupAAP == 1) {
            $wherestatement = ['group_id:label', '=', 'AAP (Robust)'];
        } elseif ($groupAAP == 2) {
            $wherestatement = ['group_id:label', '=', 'AAP (Frail)'];
        } elseif ($groupAAP == 3) {
            $wherestatement = [
                'OR', 
                [
                    ['group_id:label', '=', 'AAP (Robust)'], 
                    ['group_id:label', '=', 'AAP (Frail)']
                ]
            ];
        }        
       
        // Fetch Group Contacts
        $groupContacts = civicrm_api4('GroupContact', 'get', [
            'select' => [
              'contact_id',
            ],
            'where' => [
              $wherestatement,
            ],
            'checkPermissions' => TRUE,
        ]);

        // Iterate through each group contact to log and create activities
        foreach ($groupContacts as $groupContact) {
            if (isset($groupContact['contact_id'])) {
                $contactId = $groupContact['contact_id'];
                //Civi::log()->debug("Processing contact ID: " . $contactId);

                // Create an activity for each contact
                $results = civicrm_api4('Activity', 'create', [
                    'values' => [
                        'subject' => $newTitle,
                        'source_contact_id' => $contactId,
                        'activity_type_id' => 69,
                        'status_id' => 16,
                        'Event_Sign_up.Event_Activity' => $parentId,
                        'Event_Sign_up.Session_Id' => $activityId,
                        'Event_Sign_up.Number_of_Participants' => 1,
                        'Event_Sign_up.Attended'=> 2,
                    ],
                    'checkPermissions' => TRUE,
                ]);
            } else {
                Civi::log()->error("Missing contact_id in groupContact: " . json_encode($groupContact));
            }
        }
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