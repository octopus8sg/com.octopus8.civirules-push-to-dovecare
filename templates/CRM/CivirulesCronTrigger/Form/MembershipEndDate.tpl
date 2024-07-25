<h3>{$ruleTriggerHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-cron_trigger-block-membership_end_date">
  <div class="help">{$ruleTriggerHelp}</div>
  <div class="crm-section">
        <div class="label">{$form.membership_type_id.label}</div>
        <div class="content">{$form.membership_type_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.interval.label}</div>
        <div class="content">{$form.interval.html} {$form.interval_unit.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
