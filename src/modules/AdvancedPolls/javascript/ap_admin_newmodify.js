document.observe('dom:loaded', ap_newmodify_init_check);


function ap_newmodify_init_check()
{
    
    Event.observe('recurring',      'click', ap_newmodify_recurring);
    Event.observe('multipleselect', 'click', ap_newmodify_multipleselect);
    ap_newmodify_recurring();
    ap_newmodify_multipleselect();
}

function ap_newmodify_recurring()
{
    if($('advancedpolls_recurring').getValue() == 0) {
        $('advancedpolls_recurring_container').hide();
    } else {
        $('advancedpolls_recurring_container').show();
    }
}

function ap_newmodify_multipleselect()
{
    if($('advancedpolls_multipleselect').getValue() == 0) {
        $('advancedpolls_multipleselect_container').hide();
    } else {
        $('advancedpolls_multipleselect_container').show();
    }
}