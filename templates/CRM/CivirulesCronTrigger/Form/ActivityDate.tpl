<h3>{$ruleTriggerHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-cron_trigger-block-activity_date">
  <div class="help">{$ruleTriggerHelp}</div>
  <div class="crm-section">
    <div class="label">{$form.activity_type_id.label}</div>
    <div class="content">{$form.activity_type_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.activity_status_id.label}</div>
    <div class="content">{$form.activity_status_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.record_type.label}</div>
    <div class="content">{$form.record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.case_activity.label}</div>
    <div class="content">{$form.case_activity.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>

