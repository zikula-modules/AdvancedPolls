<h2>{$item.title|safehtml}</h2>
{include file="block/poll_results.tpl"}
<ul class="adv_pollresults">
    {if $votecounts.leadingvotename}
    <li>{gt text="Winner: %s" tag1=$votecounts.leadingvotename|safehtml domain="module_advancedpolls"}</li>
    {/if}
    <li>{gt text="Total number of votes: %s" tag1=$votecounts.totalvotecount domain="module_advancedpolls"}</li>
    <li><a href="{modurl modname="advancedpolls" type="user" func="display" pollid=$item.pollid}">{gt text="Detailed results" domain="module_advancedpolls"}</a></li>
</ul>
