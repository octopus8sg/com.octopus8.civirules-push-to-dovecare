{crmScope key='org.civicoop.civirules'}
<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-addrelatedgroup">
  <div class="help-block" id="help">
    {ts}All related contacts of the selected relationship types will be added to the selected group{/ts}.
    <br />
    <strong>{ts}Relationship type{/ts}:</strong>
    <br />
    {ts}The relationship type to find target contacts{/ts}.
  </div>
  <div class="crm-section">
    <div class="label">{$form.rel_type_ids.label}</div>
    <div class="content">{$form.rel_type_ids.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section groups-single">
    <div class="label">{$form.group_id.label}</div>
    <div class="content">{$form.group_id.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}
