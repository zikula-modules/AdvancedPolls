<div {if $blockvars.backgroundcolor neq ''}style="background-color:{$blockvars.backgroundcolor};"{/if}>
    {gt text="You have not voted in the poll" domain="module_advancedpolls"}:
    <strong>
        <a href="{modurl modname="advancedpolls" type="user" func="display" pollid=$item.pollid}">{$item.title|safehtml}</a>
    </strong>
</div>