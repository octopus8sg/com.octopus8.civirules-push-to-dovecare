<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_updatenumeric">
    <div id="help">{ts domain="org.civicoop.civirules"}This action takes the value from the contact field specified in 'Source', applies the operator and the operand, and stores the result in the field specified in 'Target'. Only numeric fields will be considered.{/ts}</div>
    <div class="crm-section">
        <div class="label">{$form.source_field_id.label}</div>
        <div class="content">{$form.source_field_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.target_field_id.label}</div>
        <div class="content">{$form.target_field_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.update_operation.label}</div>
        <div class="content">{$form.update_operation.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.update_operand.label}</div>
        <div class="content">{$form.update_operand.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
