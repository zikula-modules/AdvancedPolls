{if $ispollopen eq 1}
    {gt text="Current poll: %s" tag1=$item.title|safetext assign=templatetitle}
{else}
    {gt text="Previous poll: %s" tag1=$item.title|safetext assign=templatetitle}
{/if}
<h3>{$templatetitle}</h3>

<p>{$item.description|safehtml}</p>
<table class="z-datatable">
    <tbody>
        {foreach from=$item.options item=option}
        {if $option.optiontext}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$option.optiontext|safetext}</td>
            <td class="adv_value">
                {if $cssbars eq 1}
                <div class="progress-container">
                    {assign var="votes" value=$option.votes|@count}
                    {assign var="percentintscaled" value=$votes/$item.number_of_votes*100*$scalingfactor}
                    <div style="width: {$percentintscaled}px; background-color: {if $option.optioncolour neq ''}#{$option.optioncolour}{else}{$defaultcolour}{/if};"></div>
                </div>
                {else}
                {img modname="advancedpolls" src="bar.png" height="16" width=$percentintscaled+1 alt=$option.percent}
                {/if}
            </td>
            <td class="z-w15">{gt text="%s vote" plural="%s votes" count=$votes tag1=$votes}</td>
            <td class="z-w10">{assign var="percent" value=$votes/$item.number_of_votes*100}{$percent|formatnumber:2}%</td>
        </tr>
        {/if}
        {/foreach}
    </tbody>
</table>

<p>{gt text="Total number of votes: %s" tag1=$item.number_of_votes domain="module_advancedpolls"}</p>

{modurl modname='advancedpolls' type='user' func='display' pollid=$pollid assign=returnurl}

{notifydisplayhooks eventname='advancedpolls.ui_hooks.polls.display_view' id=$pollid}
