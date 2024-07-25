<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-contact_updatenumeric">
    <div id="help">{ts domain="org.civicoop.civirules"}This action will set the custom field to the provided value. In case of option groups, you need to provide the <code>value</code> instead of the label. You can find this in the "Option Groups" overview in the system administration menu. Complex values can be set using JSON expressions.{/ts}</div>
    <div class="crm-section">
        <div class="label">{$form.field_id.label}</div>
        <div class="content">{$form.field_id.html}</div>
        <div class="clear"></div>
    </div>
    <div class="crm-section">
        <div class="label">{$form.value.label}</div>
        <div class="content">{$form.value.html}</div>
        <div class="clear"></div>
    </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
