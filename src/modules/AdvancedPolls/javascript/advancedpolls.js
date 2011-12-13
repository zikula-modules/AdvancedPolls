/**
* Advanced Polls module for Zikula
*
* @author Advanced Polls Development Team
* @copyright (C) 2002-2011 by Mark West
* @link https://github.com/zikula-modules/AdvancedPolls
* @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* @package Zikula_3rdParty_Modules
* @subpackage Advanced_Polls
*/

/**
* Submit a poll vote
*
*@params none;
*@return none;
*/
function advancedpolls_vote()
{
    Element.update('advancedpollsvoteinfo', recordingvote);
    var pars = "module=advancedpolls&func=vote&" + Form.serialize('advancedpollsvoteform');
    var myAjax = new Ajax.Request(
    document.location.pnbaseURL+'ajax.php',
    {
        method: 'post',
        parameters: pars,
        onComplete: advancedpolls_vote_response
    });
}

/**
* Ajax response function for the vote: show the result
*
*@params none;
*@return none;
*/
function advancedpolls_vote_response(req)
{
   /* if(req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }*/
    var json = pndejsonize(req.responseText);
    Element.update('advancedpollblockcontent', json.result);
}
