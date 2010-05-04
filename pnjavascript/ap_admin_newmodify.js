/*
*  $Id$
*/
Event.observe(window, 'load', ap_newmodify_init_check, false);

function ap_newmodify_init_check()
{
    Event.observe('advancedpolls_noclosedate', 'click', ap_newmodify_closedate);
    Event.observe('advancedpolls_recurring', 'click', ap_newmodify_recurring);
    Event.observe('advancedpolls_multipleselect', 'click', ap_newmodify_multipleselect);
    ap_newmodify_closedate();
    ap_newmodify_recurring();
    ap_newmodify_multipleselect();
}

function ap_newmodify_closedate()
{
    if($('advancedpolls_noclosedate').checked == true) {
        $('advancedpolls_closedate_container').hide();
    } else {
        $('advancedpolls_closedate_container').show();
    }
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