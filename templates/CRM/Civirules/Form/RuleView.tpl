{crmScope extensionKey='org.civicoop.civirules'}
<div id="help">
  {ts}The existing CiviRules are listed below. You can manage, delete, disable/enable or add a rule. You can filter the list using the Filter Criteria{/ts}
</div>
<div class="crm-block crm-form-block crm-civirule-rule_view-block">
  {* dialog for rule help text *}
  <div class="crm-accordion-wrapper civirule-view-wrapper">
    <div class="crm-accordion-header crm-master-accordion-header">{ts}Filter Criteria{/ts}</div>
    <div class="crm-accordion-body">
      <table class="form-layout-compressed civirule-view-table">
        <tr>
          <td class="label civirule_view_trigger_id_label">{$form.trigger_id.label}</td>
          <td class="content civirule_view_trigger_id">{$form.trigger_id.html}</td>
          <td class="label civirule_view_tag_id_label">{$form.tag_id.label}</td>
          <td class="content civirule_view_tag_id">{$form.tag_id.html}</td>
        </tr><tr>
          <td class="label civirule_view_include_disabled_label">{$form.include_disabled.label}</td>
          <td class="content civirule_view_include_disabled">{$form.include_disabled.html}</td>
          <td class="label civirule_view_tag_id_label">{$form.desc_contains.label}</td>
          <td class="content civirule_view_tag_id">{$form.desc_contains.html}</td>
        </tr>
      </table>
      <div class="crm-submit-buttons">{include file="CRM/common/formButtons.tpl"}</div>
    </div>
  </div>
  {include file="CRM/Civirules/Page/RuleViewList.tpl"}
</div>
{/crmScope}
