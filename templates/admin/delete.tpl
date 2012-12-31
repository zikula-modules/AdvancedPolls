{gt text="Delete poll" assign=templatetitle}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="remove" size="small"}
    <h3>{$templatetitle}</h3>
</div>


<p class="z-warningmsg">{gt text="Do you really want to delete this poll?"}</p>
<form class="z-form" action="{modurl modname="advancedpolls" type="admin" func="delete"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="authid" value="{insert name=generateauthkey module="AdvancedPolls"}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="pollid" value="{$pollid|safetext}" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-formbuttons">
                {button src='button_ok.png' set='icons/extrasmall' __alt="Delete" __title="Delete"}
                <a href="{modurl modname=advancedpolls type=admin func=view}">{img modname='core' src='button_cancel.gif' set='icons/extrasmall'   __alt="Cancel" __title="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>

{adminfooter}