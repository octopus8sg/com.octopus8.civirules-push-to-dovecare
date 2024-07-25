<?php
/**
 * @author Wil Colón <it@unidosnow.org>
 * @license http://www.gnu.org/licenses/agpl-3.0.html
 */

class CRM_CivirulesActions_Activity_AssignNemail extends CRM_CivirulesActions_Activity_Add
{
    protected $useTriggerContact = false;

    /**
     * Method to get the api entity to process in this CiviRule action
     *
     * @access protected
     * @abstract
     */
    protected function getApiEntity()
    {
        return 'Activity';
    }

    /**
     * Method to get the api action to process in this CiviRule action
     *
     * @access protected
     * @abstract
     */
    protected function getApiAction()
    {
        return 'Create';
    }

    /**
     * Returns an array with parameters used for processing an action
     *
     * @param array $parameters
     * @param CRM_Civirules_TriggerData_TriggerData $triggerData
     * @return array
     * @access protected
     */
    protected function alterApiParameters($params, CRM_Civirules_TriggerData_TriggerData $triggerData)
    {
        $this->apiParams = $params;
        $action_params = $this->getActionParameters();
        $this->useTriggerContact = boolval($params['use_contact_trigger']);
        $assignee = array();

        $params['id'] = ($triggerData->getEntityData('Activity'))['id'];
        if ($this->useTriggerContact === true || empty($action_params['assignee_contact_id']) || !is_array($action_params['assignee_contact_id'])) {
            array_push($assignee, $triggerData->getContactId());
        } else {
            foreach ($action_params['assignee_contact_id'] as $contact_id) {
                if ($contact_id) {
                    $assignee[] = $contact_id;
                }
            }
        }

        if (count($assignee)) {
            $params['assignee_id'] = $assignee;
            $this->asignedContacts = $assignee;
        } else {
            $params['assignee_id'] = '';
        }

        $this->apiParams['send_email'] = 1; //Tell parent::processAction() to send an email

        return $params;
    }

    /**
     * Executes the action
     * Overrided to save new activity id
     *
     * This method could be overridden if needed
     *
     * @param $entity
     * @param $action
     * @param $parameters
     * @access protected
     * @throws Exception on api error
     */
    protected function executeApiAction($entity, $action, $parameters)
    {
        parent::executeApiAction($entity, $action, $parameters);
    }

    /**
     * Returns a redirect url to extra data input from the user after adding a action
     *
     * Return false if you do not need extra data input
     *
     * @param int $ruleActionId
     * @return bool|string
     * @access public
     */
    public function getExtraDataInputUrl($ruleActionId)
    {
        return CRM_Utils_System::url('civicrm/civirule/form/action/assignActivityToContact', 'rule_action_id=' . $ruleActionId);
    }

    /**
     * This function validates whether this action works with the selected trigger.
     *
     * This function could be overriden in child classes to provide additional validation
     * whether an action is possible in the current setup.
     *
     * @param CRM_Civirules_Trigger $trigger
     * @param CRM_Civirules_BAO_Rule $rule
     * @return bool
     */
    public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule)
    {
        $entities = $trigger->getProvidedEntities();
        if (isset($entities['Activity'])) {
            return true;
        }
        return false;
    }

}
