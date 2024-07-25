{crmScope key='org.civicoop.civirules'}
<h3>{$ruleActionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_action-block-activity">
  <div class="help-block" id="help">
    {ts}
      <strong>Type:</strong><br />
      <ul>
        <li><strong>Copy</strong> means that tags who are present in <em>source</em> but not in the <em>target</em> will be <em>added</em>.</li>
        <li><strong>Synchronize</strong> means that tags who are present in <em>target</em> but not in the <em>source</em> will be removed. And that tags who are present in <em>source</em> but not in the <em>target</em> will be <em>added</em>.</li>
      </ul>
      <strong>Tags:</strong><br />
      The selected tags to check.<br />
      <strong>Relationship type:</strong><br />
      The relationship type to find target contacts.<br />
    {/ts}
  </div>
  <div class="crm-section">
    <div class="label">{$form.type.label}</div>
    <div class="content">{$form.type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.tag_ids.label}</div>
    <div class="content">{$form.tag_ids.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.rel_type_ids.label}</div>
    <div class="content">{$form.rel_type_ids.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{/crmScope}
