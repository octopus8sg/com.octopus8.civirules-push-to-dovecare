    <?php

    class CRM_CivirulesActions_Activity_UpdateFieldsinAAP extends CRM_Civirules_Action {

        public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
            $contactId = $triggerData->getContactId();
            $activityId = $triggerData->getEntityId();
            // Fetch the activity details
            $activities = civicrm_api4('Activity', 'get', [
            'select' => [
                'subject', 
                'activity_date_time',
                'Event_Creation.Default_Approve_AAP',
                'Event_Creation.Is_the_AAP_conducted_at_the_AAC_space_',
                'location',                
                ],
            'where' => [
                ['id', '=', $activityId],
            ],
                'limit' => 1,
                'checkPermissions' => TRUE,
            ]);

            $recentActivity = $activities[0];

            $defaultApproveAAP = $recentActivity['Event_Creation.Default_Approve_AAP'];
            $isAAPatAAC = $recentActivity['Event_Creation.Is_the_AAP_conducted_at_the_AAC_space_'];
            $location = $recentActivity['location'];

            // Update status based on Default Approve AAP field
            $status = ($defaultApproveAAP == 1) ? 12 : 11;
  

            // Get the manager relationship
            $relationships = civicrm_api4('Relationship', 'get', [
                'select' => ['contact_id_a'],
                'where' => [
                    ['contact_id_b', '=', $contactId],
                    ['relationship_type_id', '=', 17],
                ],
                'limit' => 1,
                'checkPermissions' => TRUE,
            ]);

            $managerRelationship = $relationships[0]['contact_id_a'];

            // Get the AAC relationship
            $AACrelationships = civicrm_api4('Relationship', 'get', [
                'select' => ['contact_id_b'],
                'where' => [
                    ['relationship_type_id', '=', 5],
                    ['contact_id_a', '=', $contactId],
                    ['is_current', '=', TRUE],
                ],
                'limit' => 1,
                'checkPermissions' => TRUE,
            ]);

            $AAC = $AACrelationships[0]['contact_id_b'];

            if ($isAAPatAAC == 1) {
                $location = 'AAC Space';
            }

            // Update the activity with new details
            $results = civicrm_api4('Activity', 'update', [
                'values' => [
                'status_id' => $status,
                'assignee_contact_id' => $managerRelationship,
                'target_contact_id' => $AAC,
                'location' => $location,
                ],
                'where' => [
                ['id', '=', $activityId],
                ],
                'checkPermissions' => TRUE,
            ]);
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