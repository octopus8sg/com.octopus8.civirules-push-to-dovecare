<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_updatenumeric">
    <div id="help">{ts domain="org.civicoop.civirules"}This action will set the custom field to the value from another custom field.{/ts}</div>

  <div class="crm-section">
    <div class="label">{$form.copy_from_field_id.label}</div>
    <div class="content">{$form.copy_from_field_id.html}</div>
    <div class="clear"></div>
  </div>

    <div class="crm-section">
        <div class="label">{$form.field_id.label}</div>
        <div class="content">{$form.field_id.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
