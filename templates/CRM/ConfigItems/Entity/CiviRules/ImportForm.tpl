{crmScope extensionKey='org.civicoop.civirules'}
{if ($helpText)}
  <p class="description help">{$helpText}</p>
{/if}
{foreach from=$groups item=groupTitle key=group}
    {if (count($elements.$group))}
      <h3>{$groupTitle}</h3>
        {foreach from=$elements.$group item=element}
          <div class="crm-section">
            <div class="label">{$form.$element.label}</div>
            <div class="content">{$form.$element.html}</div>
            <div class="clear"></div>
          </div>
        {/foreach}
    {/if}
{/foreach}
{/crmScope}
