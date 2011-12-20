{include file="user/menu.tpl"}
<p class="z-informationmsg">{gt text="This poll opens on %s" tag1=$item.opendate|dateformat:datetimebrief|safetext}</p>
<h3>{gt text="Future poll: %s" tag1=$item.title|safetext}</h3>
<p>{$item.description|safehtml}</p>
<div class="z-form">
    <div class="z-fieldset">
        {foreach from=$item.options name=option item=option}
        <div class="z-formrow">
            <span class="z-label">{gt text="Choice %s" tag1=$smarty.foreach.option.iteration}:</span>
            <span>{$option.optiontext|safetext}</span>
        </div>
        {/foreach}
    </div>
</div>
{modurl modname='advancedpolls' func='display' pollid=$pollid assign=returnurl}
{* {modcallhooks hookobject='item' hookaction='display' hookid=$pollid module='AdvancedPolls' returnurl=$returnurl} *}
