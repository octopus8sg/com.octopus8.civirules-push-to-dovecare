<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-case_custom_field_changed">
  <div class="crm-section case_custom_field-section">
    <div class="label">
      <label for="case_custom_field_id">{$form.case_custom_field_id.label}</label>
    </div>
    <div class="content crm-select-container" id="case_custom_field_id_block">
      {$form.case_custom_field_id.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
