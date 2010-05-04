/**
* Advanced Polls module for Zikula
*
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2010 by Mark West
* @link http://code.zikula.org/advancedpolls
* @version $Id$
* @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
* @package Zikula_3rdParty_Modules
* @subpackage Advanced_Polls
*/

/**
* Submit a poll vote
*
*@params none;
*@return none;
*@author Mark West
*/
function advancedpolls_vote()
{
    Element.update('advancedpollsvoteinfo', recordingvote);
    var pars = "module=advanced_polls&func=vote&"
    + Form.serialize('advancedpollsvoteform');
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
*@author Mark West
*/
function advancedpolls_vote_response(req)
{
    if(req.status != 200 ) {
        pnshowajaxerror(req.responseText);
        return;
    }
    var json = pndejsonize(req.responseText);
    Element.update('advancedpollblockcontent', json.result);
}
