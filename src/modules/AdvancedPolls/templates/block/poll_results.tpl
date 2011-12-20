{if $item.options}
<dl class="adv_voteresults">
    {foreach from=$item.options key=key item=option}
    {if $option.optiontext neq ''}
    <dt>{$option.optiontext|safehtml}</dt>
    <dd>{$votecounts.percentages.$key.percent|safetext}%</dd>
    {/if}
    {/foreach}
</dl>
{/if}
