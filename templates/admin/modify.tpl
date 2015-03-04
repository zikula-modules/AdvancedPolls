{pageaddvar name="javascript" value="prototype"}
{pageaddvar name="javascript" value="modules/AdvancedPolls/javascript/prototype_colorpicker/js/prototype_colorpicker.js"}
{pageaddvar name="stylesheet" value="modules/AdvancedPolls/javascript/prototype_colorpicker/css/prototype_colorpicker.css"}
{ajaxheader modname=AdvancedPolls filename=ap_admin_newmodify.js effects=true nobehaviour=true noscriptaculous=true}


{adminheader}
<div class="z-admin-content-pagetitle">
    {icon type="edit" size="small"}
    <h3>{$templatetitle}</h3>
</div>

    
{form cssClass="z-form"}
    {formvalidationsummary}

    <fieldset>
        <legend>{gt text="Basic Information"}</legend>
        <div class="z-formrow">
            {formlabel for="title"  __text='Name of poll'}
            {formtextinput size="50" maxLength="100" id="title" mandatory=true"}
        </div>

                
        <div class="z-formrow">
            {formlabel for="urltitle"  __text='PermaLink URL title'}
            {formtextinput size="50" maxLength="120" id="urltitle"}
            <em class="z-formnote z-sub">{gt text="(Blank = auto-generate)"}</em>
        </div>
        
        {if $enablecategorization}
            <div class="z-formrow">
                {formlabel for="title"  __text='Category'}
                {nocache}
                {foreach from=$catregistry key='property' item='category'}
                {array_field_isset array=$item.__CATEGORIES__ field=$property assign='catExists'}
                {if $catExists}
                {array_field_isset array=$item.__CATEGORIES__.$property field="id" returnValue=1 assign='selectedValue'}
                {else}
                {assign var="selectedValue" value="0"}
                {/if}
                <div class="z-formlist">{selector_category category=$category name="poll[__CATEGORIES__][$property]" field="id" selectedValue=$selectedValue defaultValue="0" __defaultText='Choose Category'}</div>
                {/foreach}
                {/nocache}
            </div>
        {/if}
                
        <div class="z-formrow">
            {formlabel for="description"  __text='Description'}
            {formtextinput textMode="multiline" size="50" maxLength="100" id="description" rows="10" cols="50"}
        </div>
        
        <div class="z-formrow">
            {formlabel for="language"  __text='Language'}
            {html_select_languages id="language" name="poll[language]" installed=true all=true selected=$language}
        </div>
    </fieldset>
        
    <fieldset>
        <legend>{gt text="Timing"}</legend>
        
        <div class="z-formrow">
            {formlabel for="opendate"  __text='Date and time poll opens'}
            {formdateinput id="opendate" useSelectionMode=0 ifFormat='%Y-%m-%d %H:%M:%S' includeTime=1}
        </div>
            
        
        <div class="z-formrow">
            {formlabel for="closedate"  __text='Date and time poll closes'}
            {formdateinput id="closedate" useSelectionMode=0 ifFormat=$dateformat  initDate='%Y-%m-%d %H:%M:%S' includeTime=1}
        </div>
            
        <div class="z-formrow">
            {formlabel for="recurring" __text='Recurring poll?'}
            {formdropdownlist id="recurring" items=$recurring_types}
        </div>
        <div id="advancedpolls_recurring_container">
            <p class="z-formnote z-informationmsg">{gt text="The following options are only relevant if a recurring poll is selected."}</p>
            <div class="z-formrow">
                {formlabel for="recurringoffset" __text='Recurring offset'}
                {formtextinput size="5" maxLength="5" id="recurringoffset"}
                <em class="z-sub z-formnote">{gt text="Number of hours after the close date the poll reopens."}</em>
            </div>
            <div class="z-formrow">
                {formlabel for="recurringinterval" __text='Recurrence interval'}
                {formtextinput size="5" maxLength="5" id="recurringinterval"}
                <em class="z-sub z-formnote">{gt text="Number of days the poll will recur."}</em>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text="Voting regulations"}</legend>
        <div class="z-formrow">
            {formlabel for="voteauthtype" __text='Poll authorisation method'}
            {formdropdownlist id="voteauthtype" items=$voteauthtype_types}
        </div>
        <div class="z-formrow">
            {formlabel for="tiebreakalg" __text='Tiebreak method'}
            {formdropdownlist id="tiebreakalg" items=$tiebreakalg_types}
        </div>
        <div class="z-formrow">
            {formlabel for="multipleselect" __text='Selection method'}
            {formdropdownlist id="multipleselect" items=$multipleselect_types}
        </div>
        <div id="advancedpolls_multipleselect_container">
            <p class="z-formnote z-informationmsg">{gt text="The following option only relevant if a multiple selection poll is selected."}</p>
            <div class="z-formrow">
                {formlabel for="multipleselectcount" __text='You may select '}
                {formtextinput size="5" maxLength="5" id="multipleselectcount"}
                <em class="z-formnote z-sub">{gt text="(-1 for Unlimited selections)"}</em>
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text="Options"}</legend>
        
        {foreach from=$options item="option" name="options"}
            <div class="z-formrow">
                {assign var="i" value=$smarty.foreach.options.iteration}
                {formlabel __text="Poll option"|cat:" "|cat:$i}
                <div style="white-space:nowrap">
                    {formtextinput size="50" maxLength="255" id="optiontext_$i"  group="option_texts" text=$option.optiontext}
                    {formtextinput size="6"  maxLength="6"   id="optioncolor_$i" group="option_colors" text=$option.optioncolour}
                </div>
            </div>
            <script type="text/javascript">
                var cp1 = new colorPicker(
                    'optioncolor_{{$i}}',
                    {
                        color:'#{{$option.optioncolour}}',
                        previewElement:'optioncolor_{{$i}}'
                    }
                );
            </script>  
        {/foreach}
        
        {section name="i" start=1 loop=$newoptioncount+1 step=1}
            {assign var="j" value=$smarty.section.i.index}
             <div class="z-formrow">
                {if $j == 1}
                    {formlabel __text="New poll options"}
                {else}
                    {formlabel text=""}
                {/if}
                <div style="white-space:nowrap">
                    {formtextinput size="50" maxLength="255" id="optiontext_n$j"  group="option_texts"}
                    {formtextinput size="6"  maxLength="6"   id="optioncolor_n$j" group="option_colors"}
                </div>
            </div>
                
            <script type="text/javascript">
                var cp1 = new colorPicker(
                    'optioncolor_n{{$j}}',
                    {
                        color:'',
                        previewElement:'optioncolor_n{{$j}}'
                    }
                );
            </script>   
        {/section}
    </fieldset>

    <fieldset>
        <legend>{gt text="Meta data"}</legend>
        <ul style="margin: 7px 0px 7px 20px">
            {usergetvar name=uname uid=$cr_uid assign=username}
            <li>{gt text="Created by %s" tag1=$username}</li>
            <li>{gt text="Created on %s" tag1=$cr_date|dateformat}</li>
            {usergetvar name=uname uid=$lu_uid assign=username}
            <li>{gt text="Last updated by %s" tag1=$username}</li>
            <li>{gt text="Updated on %s" tag1=$lu_date|dateformat}</li>
        </ul>
    </fieldset>

    {notifydisplayhooks eventname='advancedpolls.ui_hooks.polls.form_edit' id=$pollid}
    
    <div class="z-formbuttons z-buttons">
        {formbutton class="z-bt-ok" commandName="save" __text="Save"}
        {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
    </div>
{/form}
{adminfooter}