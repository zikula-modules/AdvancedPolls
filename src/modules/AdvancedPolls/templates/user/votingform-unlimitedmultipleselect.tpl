<p class="z-informationmsg">
    {gt text="This is an unlimited multiple selection poll." domain="module_advancedpolls"}
    {gt text="You may select any number of options." domain="module_advancedpolls"}
</p>

{counter name=counter print=false start=0}
{foreach from=$item.options item=option}
<div class="z-formnote">
    <input id="option{$option.optionid|safetext}" type="checkbox" name="option{counter}" value="{$option.optionid|safetext}" />
    {if $option.optioncolour neq ''}
    <span style="color:{$option.optioncolour};">
        <label id="option{$option.optionid|safetext}">{$option.optiontext|modcallhooks|safehtml}</label>
    </span>
    {else}
    <label for="option{$option.optionid|safetext}">{$option.optiontext|modcallhooks|safehtml}</label>
    {/if}
</div>
{/foreach}