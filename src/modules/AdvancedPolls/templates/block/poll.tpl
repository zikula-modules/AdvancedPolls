<div id="advancedpollblockcontent">
    <h2>{$item.title|safetext}</h2>
    <p>{$item.description|safehtml}</p>

    {if $ispollopen and $isvoteallowed}

    {if $blockvars.ajaxvoting}
    {ajaxheader modname=advancedpolls filename=advancedpolls.js}
    {/if}

    <form id="advancedpollsvoteform" class="z-form" action="{modurl modname="advancedpolls" type="user" func="vote"}" method="post" enctype="application/x-www-form-urlencoded">
        <div>
            <input type="hidden" name="authid" value="{insert name=csrftoken module="advancedpolls"}" />
            <input type="hidden" name="pollid" value="{$pollid|safetext}" />
            <input type="hidden" name="title" value="{$item.title|safehtml}" />
            <input type="hidden" name="results" value="1" />
            <input type="hidden" name="polldisplayresults" value="{$blockvars.polldisplayresults|safetext}" />
            <input type="hidden" name="returnurl" value="{$returnurl|safetext}" />
            <fieldset>
                <legend>{gt text="Poll options"}</legend>
                {if $polltype eq 0}
                {include file="user/votingform-singleoptionselect.tpl"}
                {else}
                <input type="hidden" name="multiple" value="1" />
                <input type="hidden" name="multiplecount" value="{$multiplecount}" />
                {if $polltype eq 1}
                {if $multiplecount eq -1}
                {include file="user/votingform-unlimitedmultipleselect.tpl"}
                {else}
                {include file="user/votingform-multipleselect.tpl"}
                {/if}
                {else}
                {include file="user/votingform-rankedmultipleselect.tpl"}
                {/if}
                {/if}
            </fieldset>
            <div class="z-formbuttons">
                {if $blockvars.ajaxvoting}
                <input onclick="javascript:advancedpolls_vote();" name="vote" type="button" value="{gt text="Vote" domain="module_advancedpolls"}" />
                {else}
                <input name="submit" type="submit" value="{gt text="Vote" domain="module_advancedpolls"}" />
                {/if}
            </div>
        </div>
    </form>

    {if $blockvars.ajaxvoting}
    <div id="advancedpollsvoteinfo" style="color:red;">&nbsp;</div>
    <script type="text/javascript">
    var recordingvote = '<img src="images/ajax/indicator_circle.gif" />';
    </script>
    {/if}

    {else}
    {include file="block/poll_results.tpl"}
    {/if}

    <ul class="adv_polldetails">
        {if $ispollopen and $isvoteallowed}
        {else}
        {if $ispollopen}
        {if $votecounts.totalvotecount > 0}
        <li><strong>{gt text="The current leader in this poll is %s" tag1=$votecounts.leadingvotename|safehtml domain="module_advancedpolls"}</strong></li>
        {if $item.pollnoclosedate neq 0}
        <li>{gt text="Poll close date: %s" tag1=$item.closedate|dateformat:datetimebrief domain="module_advancedpolls"}</li>
        {/if}
        {/if}
        {else}
        {if $votecounts.totalvotecount > 0}
        <li>{gt text="Winner: %s" tag1=$votecounts.leadingvotename|safehtml domain="module_advancedpolls"}</li>
        {/if}
        {/if}
        {/if}
        <li>{gt text="Total number of votes: %s" tag1=$votecounts.totalvotecount|default:0 domain="module_advancedpolls"}</li>
        <li><a href="{modurl modname="advancedpolls" type="user" func="display" pollid=$pollid}">{gt text="Detailed results" domain="module_advancedpolls"}</a></li>
    </ul>
</div>
