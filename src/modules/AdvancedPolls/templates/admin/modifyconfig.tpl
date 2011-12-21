{pageaddvar name="javascript" value="prototype"}
{pageaddvar name="javascript" value="modules/AdvancedPolls/javascript/prototype_colorpicker/js/prototype_colorpicker.js"}
{pageaddvar name="stylesheet" value="modules/AdvancedPolls/javascript/prototype_colorpicker/css/prototype_colorpicker.css"}

{adminheader}
{gt text="Settings" assign=templatetitle}
<div class="z-admin-content-pagetitle">
    {icon type="config" size="small"}
    <h3>{$templatetitle}</h3>
</div>


{form cssClass="z-form"}
    {formvalidationsummary}
    <fieldset>
        <legend>{gt text="General settings"}</legend>
        <div class="z-formrow">
            {formlabel for="enablecategorization" __text="Enable categorization"}
            {formcheckbox id="enablecategorization"}
        </div>
        <div class="z-formrow">
            {formlabel for="usereversedns" __text="Use Reverse DNS for IP Addresses"}
            {formcheckbox id="usereversedns"}            
        </div>
        <div class="z-formrow">
            {formlabel for="adminitemsperpage" __text="Items per page in admin interface"}
            {formtextinput id="adminitemsperpage" size="3" maxLength="3"}
        </div>
    </fieldset>
    <fieldset>
        <legend>{gt text="Poll settings"}</legend>
        <div class="z-formrow">
            {formlabel for="cssbars" __text="Use css poll results bars"}
            {formcheckbox id="cssbars"}
        </div>
        <div class="z-formrow">
            {formlabel for="scalingfactor" __text="Scaling factor for poll results bars"}
            {formtextinput id="scalingfactor" size="3" maxLength="3"}
        </div>
        <div class="z-formrow">
            {formlabel for="defaultoptioncount" __text="Default number of options in a poll"}
            {formtextinput id="defaultoptioncount"  size="3" maxLength="3"}
        </div>
        <div class="z-formrow">
            {formlabel for="defaultcolor" __text="Default colour"}
            <table>
                <tr>
                    <td id="defaultcolour-preview" class="colour-preview" width=80>
                        {formtextinput id="defaultcolour" size="6" maxLength="6"}
                    </td>
                </tr>
            </table>
        </div>
    </fieldset>

    {* {modcallhooks hookobject=module hookaction=modifyconfig module=AdvancedPolls} *}
    <div class="z-formbuttons z-buttons">
        {formbutton class="z-bt-ok" commandName="save" __text="Save"}
        {formbutton class="z-bt-cancel" commandName="cancel" __text="Cancel"}
    </div>

        
  <script type="text/javascript">
    var cp1 = new colorPicker(
        'defaultcolour',
        {
            color:'#{{$defaultcolour}}',
            previewElement:'defaultcolour-preview'
        }
    );
</script>
      
        

	
	
{/form}
{adminfooter}