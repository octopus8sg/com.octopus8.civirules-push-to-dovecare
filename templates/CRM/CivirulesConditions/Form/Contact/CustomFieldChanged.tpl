<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-custom_field_changed">
  <div class="crm-section custom_field-section">
    <div class="label">
      <label for="custom_field_id">{$form.custom_field_id.label}</label>
    </div>
    <div class="content crm-select-container" id="custom_field_id_block">
      {$form.custom_field_id.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
