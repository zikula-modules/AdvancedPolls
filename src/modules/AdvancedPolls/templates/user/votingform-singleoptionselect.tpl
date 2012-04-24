{foreach from=$item.options item=option}
<div class="z-formnote">
    <input id="option{$option.optionid|safehtml}" type="radio" name="option{$pollid|safetext}" value="{$option.optionid|safetext}" />
    {if $option.optioncolour neq ''}
    <span style="color:{$option.optioncolour};">
        <label for="option{$option.optionid|safehtml}">{$option.optiontext|safehtml}</label>
    </span>
    {else}
    <label for="option{$option.optionid|safehtml}">{$option.optiontext|safehtml}</label>
    {/if}
</div>
{/foreach}
