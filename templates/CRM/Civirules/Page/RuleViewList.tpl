{crmScope extensionKey='org.civicoop.civirules'}
<div class="crm-content-block crm-block">
</div>
<div class="action-link">
  <a class="button new-option civirule-add-new" href="{$add_url}">
    <span><i class="crm-i fa-plus-circle"></i> {ts}Add CiviRule{/ts}</span>
  </a>
</div>

{* dialog for rule help text *}
<div id="civirule_helptext_dialog-block">
  <p><label id="civirule_help_text-value"></label></p>
</div>

<div id="civirule_wrapper" class="dataTables_wrapper">
  {include file="CRM/common/jsortable.tpl"}
  {include file="CRM/common/enableDisableApi.tpl"}
  <table id="civirule-table" class="display">
    <thead>
    <tr>
      <th id="sortable">{ts}ID{/ts}</th>
      <th id="sortable">{ts}Label{/ts}</th>
      <th id="sortable">{ts}Trigger{/ts}</th>
      <th id="sortable">{ts}Tag(s){/ts}</th>
      <th id="nosort">{ts}Description{/ts}</th>
      <th id="sortable">{ts}Enabled?{/ts}</th>
      <th id="sortable">{ts}Date modified{/ts}</th>
      <th id="sortable">{ts}Modified by{/ts}</th>
      <th id="sortable">{ts}Last triggered{/ts}</th>
      <th id="sortable">{ts}Triggered for{/ts}</th>
      <th id="nosort"></th>
    </tr>
    </thead>
    <tbody>
    {assign var="row_class" value="odd-row"}
    {foreach from=$rules key=rule_id item=row}
      <tr id="row_{$rule_id}" class="crm-entity {cycle values="odd-row,even-row"}{if !$row.enabled} disabled{/if}">
        <td>{$rule_id}</td>
        <td>{$row.label}</td>
        <td>{$row.trigger_label}</td>
        <td>{$row.tags}</td>
        <td>{$row.description}
          {if (!empty($row.help_text))}
            <a id="civirule_help_text_icon" class="crm-popup medium-popup helpicon" onclick="showRuleHelp({$rule_id})" href="#"></a>
          {/if}
        </td>
        <td>{$row.is_active}</td>
        <td>{$row.modified_date}</td>
        <td>{$row.modified_by}</td>
        <td>{$row.last_trigger_date}</td>
        <td>{$row.last_trigger_contact}</td>
        <td>
              <span>
                {foreach from=$row.actions item=action_link}
                  {$action_link}
                {/foreach}
              </span>
        </td>
      </tr>
    {/foreach}
    </tbody>
  </table>
</div>
<div class="action-link">
  <a class="button new-option civirule-add-new" href="{$add_url}">
    <span><i class="crm-i fa-plus-circle"></i> {ts}Add CiviRule{/ts}</span>
  </a>
</div>

{literal}
  <script>
    function showRuleHelp(ruleId) {
      console.log('rule id is ' + ruleId);
      CRM.api3('CiviRuleRule', 'getsingle', {"id": ruleId})
              .done(function(result) {
                cj("#civirule_helptext_dialog-block").dialog({
                  width: 600,
                  height: 300,
                  title: "Help for Rule " + result.label,
                  buttons: {
                    "Done": function() {
                      cj(this).dialog("close");
                    }
                  }
                });
                cj("#civirule_helptext_dialog-block").html(result.help_text);
              });
    }

    function civiruleEnableDisable(ruleId, action) {
      if (action === 1) {
        CRM.api3('CiviRuleRule', 'getClones', {"id": ruleId})
                .done(function (result) {
                  if (result.count > 0) {
                    location.href = CRM.url('civicrm/civirule/form/ruleenable', {"id": ruleId});
                  }
                  else {
                    CRM.api3('CiviRuleRule', 'create', {"id": ruleId, "is_active": action})
                            .done(function (result) {
                              location.reload(true);
                            });
                  }
                });
      }
      else {
        CRM.api3('CiviRuleRule', 'create', {"id": ruleId, "is_active": action})
                .done(function (result) {
                  location.reload(true);
                });
      }
    }
  </script>
{/literal}
{/crmScope}
