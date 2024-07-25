{crmScope extensionKey='org.civicoop.civirules'}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>

{if $action eq 8}
  {* Are you sure to delete form *}
  <h3>{ts}Delete rule{/ts}</h3>
  <div class="crm-block crm-form-block crm-civirule-rule_label-block">
    <div class="crm-section">{ts 1=$rule->label}Are you sure to delete rule '%1'?{/ts}</div>
  </div>
{else}
  {include file="CRM/Civirules/Form/RuleBlocks/RuleBlock.tpl"}
  {include file="CRM/Civirules/Form/RuleBlocks/TriggerBlock.tpl"}
  {if $action ne 1}
    {include file="CRM/Civirules/Form/RuleBlocks/ConditionBlock.tpl"}
    {include file="CRM/Civirules/Form/RuleBlocks/ActionBlock.tpl"}
  {/if}
{/if}

{if $action ne 1}
  <div class="crm-accordion-wrapper collapsed">
    <div class="crm-accordion-header" id="civirule-trigger-history">
      {ts}Last 20 triggers for this rule{/ts}
    </div>
    <div class="crm-accordion-body">
      <div class="crm-section">
        <div id="civirule_wrapper" class="dataTables_wrapper">
          <table id="civirule-table" class="display">
            <thead>
            <tr>
              <th id="sortable">{ts}Last triggered{/ts}</th>
              <th id="sortable">{ts}Triggered for{/ts}</th>
            </tr>
            </thead>
            <tbody>

            {assign var="rowClass" value="odd_row"}
            {assign var="rowNumber" value=1}
            {foreach from=$ruleTriggerHistory item=row}
              <tr class="{cycle values="odd-row,even-row"} trigger-history-row">
                <td>{$row.last_trigger_date}</td>
                <td>{$row.last_trigger_contact_link}</td>
              </tr>
            {/foreach}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
{/if}

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
{/crmScope}
