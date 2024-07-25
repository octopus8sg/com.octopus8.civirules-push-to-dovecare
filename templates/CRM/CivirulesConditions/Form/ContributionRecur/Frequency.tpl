<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-contribution_recur_frequency">
  <div class="crm-section">
    <div class="label">{$form.frequency_interval.label}</div>
    <div class="content">{$form.frequency_interval.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section">
    <div class="label">
      <label for="frequency_unit-select">{$form.frequency_unit.label}</label>
    </div>
    <div class="content crm-select-container" id="frequency_unit_block">
      {$form.frequency_unit.html}
    </div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
