<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-{$ruleName}">
  <div class="help">{$ruleConditionHelp}</div>
    <h4>{$form.payment_processor_id.label}</h4>
    <div class="crm-section">
        <div class="label">{$form.payment_processor_id_operator.html}</div>
        <div class="content">{$form.payment_processor_id.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
