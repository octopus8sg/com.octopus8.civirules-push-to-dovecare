<?php
/**
 * Class for CiviRules Activity Edit Action Form
 *
 * @author David Hayes (Black Brick Software) <david@blackbrick.software>
 * @license AGPL-3.0
 */

class CRM_CivirulesActions_Activity_Form_ActivityEdit extends CRM_CivirulesActions_Activity_Form_Activity {

  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    
    parent::buildQuickForm();
    
    // remove required fields
    
    if ($this->elementExists('activity_type_id'))
      $this->removeElement('activity_type_id');

    if ($this->elementExists('status_id'))
      $this->removeElement('status_id');

    // add back previously removed fields as not required
    $this->add('select', 'activity_type_id', ts('Activity type'), ['' => ts('-- please select --')] + CRM_Core_OptionGroup::values('activity_type'), false);
    $this->add('select', 'status_id', ts('Status'), ['' => ts('-- please select --')] + CRM_Core_OptionGroup::values('activity_status'), false);
  }
}
