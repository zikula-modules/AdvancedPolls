{gt text="Voting Statistics" assign=templatetitle}
{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="view" size="small"}
    <h3>{$templatetitle}</h3>
</div>


<strong>{gt text="Name of poll"}:</strong> {$item.title|safehtml}<br />
<strong>{gt text="Date and time poll opens"}:</strong> {$item.opendate|dateformat:datetimebrief|safetext}<br />
<strong>{gt text="Date and time poll closes"}:</strong> {$item.closedate|dateformat:datetimebrief|safetext}<br />
<strong>{gt text="Total number of votes"}:</strong> {$votecount|safetext|default:'0'}<br />

<form class="z-form" action="{modurl modname="advancedpolls" type="admin" func="adminstats"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="authid" value="{insert name=csrftoken module="advancedpolls"}" />
        <input type="hidden" name="pollid" value="{$pollid|safetext}" />
        <fieldset>
            <label for="advancedpolls_sortby">{gt text="Sort votes by"}</label>
            <select id="advancedpolls_sortby" name="sortby">
                {sortbytypes selected=$sortby}
            </select>
            <label for="advancedpolls_sortorder">{gt text="Sort order"}</label>
            <select id="advancedpolls_sortorder" name="sortorder">
                {sortordertypes selected=$sortorder}
            </select>
            <input type="submit" value="{gt text="Sort votes"}" />
        </fieldset>
    </div>
</form>

<h3>{gt text="Vote history"}</h3>
<table class="z-admintable">
    <thead>
        <tr>
            <th>{gt text="Vote ID"}</th>
            <th>{gt text="IP address"}</th>
            <th>{gt text="Time"}</th>
            <th>{gt text="Username"}</th>
            <th>{gt text="Vote rank"}</th>
            <th>{gt text="Option"}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$votes item=vote}
        <tr class="{cycle values=z-odd,z-even}">
            <td>{$vote.voteid|safetext}</td>
            <td>{$vote.ip|safetext}</td>
            <td>{$vote.time|dateformat:datetimebrief|safetext}</td>
            <td>{$vote.user|safetext}</td>
            <td>{$vote.voterank|safetext}</td>
            <td>{$vote.optiontext|safetext}</td>
        </tr>
        {foreachelse}
        <tr class="z-admintableempty"><td colspan="6">{gt text="No votes found."}</td></tr>
        {/foreach}
    </tbody>
</table>
{pager rowcount=$pager.numitems limit=$pager.adminitemsperpage posvar=startnum shift=1}


{adminfooter}