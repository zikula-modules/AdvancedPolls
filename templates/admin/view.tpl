{adminheader}
{gt text="View Polls" assign=templatetitle}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{$templatetitle}</h3>
</div>



{if $enablecategorization and $numproperties gt 0}
<form class="z-form" action="{modurl modname=advancedpolls type=admin func=view}" method="post" enctype="application/x-www-form-urlencoded">
    <fieldset id="advanced_polls_multicategory_filter">
        <label for="advanced_polls_{$property}_category">{gt text="Category"}</label>
        {nocache}
        {if $numproperties gt 1}
        {html_options id="advanced_polls_property" name="advanced_polls_property" options=$properties selected=$property}
        {else}
        <input type="hidden" id="advanced_polls_property" name="advanced_polls_property" value="{$property}" />
        {/if}
        {foreach from=$catregistry key=prop item=cat}
        {assign var=propref value=$prop|string_format:'advanced_polls_%s_category'}
        {if $property eq $prop}
        {assign var="selectedValue" value=$category}
        {else}
        {assign var="selectedValue" value=0}
        {/if}
        <noscript>
            <div class="property_selector_noscript"><label for="{$propref}">{$prop}</label>:</div>
        </noscript>
        {selector_category category=$cat name=$propref selectedValue=$selectedValue allValue=0 __allText='Choose One' editLink=false}
        {/foreach}
        {/nocache}
        <input name="submit" type="submit" value="{gt text="Filter"}" />
        <input name="clear" type="submit" value="{gt text="Reset"}" />
    </fieldset>
</form>
{/if}
<table class="z-admintable">
    <thead>
        <tr>
            <th>{gt text="Poll ID"}</th>
            <th>{gt text="Name of poll"}</th>
            {if $enablecategorization}
            <th>{gt text="Category"}</th>
            {/if}
            <th>{gt text="Poll open date"}</th>
            <th>{gt text="Is poll open?"}</th>
            <th>{gt text="Options"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$polls item=poll}
        <tr class="{cycle values=z-odd,z-even}">
            <td>{$poll.pollid|safetext}</td>
            <td>
                <a href="{modurl modname="advancedpolls" type="user" func="display" pollid=$poll.pollid}">{$poll.title|safetext}</a>
            </td>
            {if $enablecategorization}
            <td>
                {foreach from=$poll.__CATEGORIES__ name=cat item=cat}
                {array_field assign="catname" array=$cat.display_name field=$lang returnValue=1}
                {if $catname eq ''}{assign var="catname" value=$cat.name}{/if}
                {$catname}
                {if !$smarty.foreach.cat.last}<br />{/if}
                {/foreach}
            </td>
            {/if}
            <td>{$poll.opendate|dateformat:datetimebrief}</td>
            <td>{$poll.isopen|yesno}</td>
            <td class="z-nowrap">
                {assign var="options" value=$poll.options}
                {section name=options loop=$options}
                <a href="{$options[options].url|safetext}">{img modname='core' set='icons/extrasmall' src=$options[options].image title=$options[options].title alt=$options[options].title}</a>
                {/section}
            </td>
        </tr>
        {foreachelse}
        <tr class="z-admintableempty"><td colspan="{if $enablecategorization}6{else}5"{/if}">{gt text="No polls found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{* {pager rowcount=$pager.numitems limit=$pager.adminitemsperpage posvar=startnum shift=1} *}

{adminfooter}