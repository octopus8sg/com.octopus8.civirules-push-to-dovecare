<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-contact_has_type">
    <div class="crm-section">
      <div class="label">{$form.operator.label}</div>
      <div class="content">{$form.operator.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section sector-section">
      <div class="label">
        <label for="contact_type-select">{ts}Contact Type(s){/ts}</label>
      </div>
      <div class="content crm-select-container" id="contact_type_block">
        {$form.type_names.html}
      </div>
      <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>