<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-in-group-since">
  <div class="crm-section in-group-since-group-id-section">
    <div class="label">{$form.group_id.label}</div>
    <div class="content">{$form.group_id.html}</div>
    <div class="clear"></div>
</div>
  <div class="crm-section in-group-since-operator-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section in-group-since-number-section">
    <div class="label">{$form.number.label}</div>
    <div class="content">{$form.number.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section in-group-since-period-section">
    <div class="label">{$form.period.label}</div>
    <div class="content">{$form.period.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
