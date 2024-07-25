{crmScope extensionKey='org.civicoop.civirules'}
{if ($helpText)}
    <p class="description help">{$helpText}</p>
{/if}
{if (count($elements))}
  <h3>{ts 1=$entityTitle}%1 on this system{/ts}</h3>
  {foreach from=$elements item=element}
  <div class="crm-section">
    <div class="label">{$form.$element.label}</div>
    <div class="content">{$form.$element.html}</div>
    <div class="clear"></div>
  </div>
  {/foreach}
{/if}

{if (count($non_existing_elements))}
  <h3>{ts 1=$entityTitle}%1 in the import file{/ts}</h3>
  <p class="description">{ts 1=$entityTitle}The following %1 are available in the import file but not on this system.{/ts}</p>
  {foreach from=$non_existing_elements item=element}
    <div class="crm-section">
      <div class="label">{$form.$element.label}</div>
      <div class="content">{$form.$element.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}
{/if}
{/crmScope}
