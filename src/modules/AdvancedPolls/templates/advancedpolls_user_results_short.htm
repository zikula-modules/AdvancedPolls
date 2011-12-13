<h3>
    {if $ispollopen eq 1}
    {gt text="Current poll" domain="module_advancedpolls"}: {$item.title|safetext}
    {else}
    {gt text="Previous poll" domain="module_advancedpolls"}: {$item.title|safeteext}
    {/if}
</h3>
<p>{$item.description|safethml}</p>
<table class="z-datatable">
    <tbody>
        {foreach from=$item.options item=option}
        {if $option.optiontext}
        <tr class="{cycle values="z-odd,z-even"}">
            <td>{$option.optiontext|safehtml}</td>
            <td class="adv_value">
                {if $cssbars eq 1}
                <div class="progress-container">
                    <div style="width: {$option.percentintscaled}px; background-color: {if $option.optioncolour neq ''}{$option.optioncolour}{else}{$defaultcolour}{/if};"></div>
                </div>
                {else}
                {img modname="advancedpolls" src="bar.png" height="16" width=$option.percentintscaled+1 alt=$option.percent}
                {/if}
            </td>
            <td class="z-w15">{gt text="%s vote" plural="%s votes" count=$option.votecount tag1=$option.votecount}</td>
            <td class="z-w10">{$option.percent|formatnumber:2}%</td>
        </tr>
        {/if}
        {/foreach}
    </tbody>
</table>

<p>{gt text="Total number of votes: %s" tag1=$votecount.totalvotecount domain="module_advancedpolls"}</p>