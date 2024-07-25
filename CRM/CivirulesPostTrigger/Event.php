<?php
/**
 * Copyright (C) 2021  Jaap Jansma (jaap.jansma@civicoop.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

use CRM_Civirules_ExtensionUtil as E;

/**
 * Class CRM_CivirulesPostTrigger_Event
 *
 * Use a custom class for event triggers because we will optionally add the logged in user
 * as the contact of the trigger.
 *
 */
class CRM_CivirulesPostTrigger_Event extends CRM_Civirules_Trigger_Post {

  protected function getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID = NULL) {
    $triggerData = parent::getTriggerDataFromPost($op, $objectName, $objectId, $objectRef, $eventID);
    if (isset($this->triggerParams['contact_id']) && $this->triggerParams['contact_id'] == 1) {
      $contactId = CRM_Core_Session::getLoggedInContactID();
      $triggerData->setContactId($contactId);
    }
    return $triggerData;
  }

  /**
   * Returns a redirect url to extra data input from the user after adding a trigger
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/civirule/form/trigger/event', 'rule_id='.$ruleId);
  }

  /**
   * Returns a description of this trigger
   *
   * @return string
   */
  public function getTriggerDescription(): string {
    if (isset($this->triggerParams['contact_id']) && $this->triggerParams['contact_id'] == 1) {
      return E::ts('Trigger uses the logged in user as the contact.');
    } else {
      return E::ts('Trigger does not use a contact.');
    }
  }

}
