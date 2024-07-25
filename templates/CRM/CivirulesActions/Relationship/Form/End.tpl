<h3>{$ruleActionHeader}</h3>
<div id="help">
  {ts}This action will disable or delete relationship(s) of  the selected type where it is <strong>assumed</strong> that the contact in question is <strong>contact A</strong>. <br />
  As you know a relationship in CiviCRM is between 2 contacts, for example the empolyee of/employer is relationship where the employee is contact A and the employer is contact B.
  <br /><br />
    It is relationship(s) because a contact can have more than 1 relationship of a certain type, <strong>all</strong> of those will be disabled or deleted.
  <br /><br />
  You can select the relationship type and if the relationship should be disabled (and show up as a former relationship) or deleted (completely removed from the database).
  If you select to disable the relationship, you can also select the end date of the relationship. The default will be the date the action is executed.{/ts}
</div>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-relationship-end">
  <div class="crm-section">
    <div class="label">{$form.relationship_type_id.label}</div>
    <div class="content">{$form.relationship_type_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.operation.label}</div>
    <div class="content">{$form.operation.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.end_date.label}</div>
    <div class="content">{$form.end_date.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
