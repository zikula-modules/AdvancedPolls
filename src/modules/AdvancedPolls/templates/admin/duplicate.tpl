{gt text="Duplicate poll" assign=templatetitle}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="editcopy" size="small"}
    <h3>{$templatetitle}</h3>
</div>


<p class="z-informationmsg">{gt text="Please confirm duplication of poll."}</p>
<form class="z-form" action="{modurl modname="advancedpolls" type="admin" func="duplicate"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="authid" value="{insert name=csrftoken module="advancedpolls"}" />
        <input type="hidden" name="confirmation" value="1" />
        <input type="hidden" name="pollid" value="{$pollid|safetext}" />
        <fieldset>
            <legend>{gt text="Confirmation prompt"}</legend>
            <div class="z-formbuttons">
                {button src='button_ok.gif' set='icons/extrasmall' __alt="Duplicate poll" __title="Duplicate poll"}
                <a href="{modurl modname=advancedpolls type=admin func=view}">{img modname='core' src='button_cancel.png' set='icons/extrasmall'   __alt="Cancel" __title="Cancel"}</a>
            </div>
        </fieldset>
    </div>
</form>

            
{adminfooter}