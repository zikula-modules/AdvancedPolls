{setmetatag name='description' value=$item.description|strip_tags|trim|truncate:255}
{gt text="Voting booth: %s" tag1=$item.title|safetext assign=templatetitle}
<h3>{$templatetitle}</h3>
<p>{$item.description|safehtml}</p>
<form class="z-form" id="advanced_polls_admin_modify" action="{modurl modname='advancedpolls' type='user' func='vote'}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="authid" value="{insert name=csrftoken module="advancedpolls"}" />
        <input type="hidden" name="pollid" value="{$pollid|safetext}" />
        <input type="hidden" name="title" value="{$item.title|safehtml}" />
        <input type="hidden" name="results" value="1" />
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
            <input name="submit" type="submit" value="{gt text="Vote"}" />
        </div>
    </div>
</form>
{modurl modname='advancedpolls' type='user' func='display' pollid=$pollid assign=returnurl}
{* {modcallhooks hookobject='item' hookaction='display' hookid=$pollid module='AdvancedPolls' returnurl=$returnurl} *}