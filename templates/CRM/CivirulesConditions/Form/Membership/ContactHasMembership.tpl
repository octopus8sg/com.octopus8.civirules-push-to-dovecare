<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-contact_has_membership">
    <div class="crm-section">
        <div class="label">{$form.inclusion_operator.label}</div>
        <div class="content">{$form.inclusion_operator.html}</div>
        <div class="clear"></div>
    </div>
    <h4>{$form.membership_type_id.label}</h4>
    <div class="crm-section">
        <div class="label">{$form.type_operator.html}</div>
        <div class="content">{$form.membership_type_id.html}</div>
        <div class="clear"></div>
    </div>
    <h4>{$form.membership_status_id.label}</h4>
    <div class="crm-section">
        <div class="label">{$form.status_operator.html}</div>
        <div class="content">{$form.membership_status_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.start_date_relative.label}</div>
        <div class="content">
          {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName='start_date' hideRelativeLabel=1 from='_from' to='_to'}
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.join_date_relative.label}</div>
        <div class="content">
          {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName='join_date' hideRelativeLabel=1 from='_from' to='_to'}
        </div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.end_date_relative.label}</div>
        <div class="content">
          {include file="CRM/Core/DatePickerRangeWrapper.tpl" fieldName='end_date' hideRelativeLabel=1 from='_from' to='_to'}
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
