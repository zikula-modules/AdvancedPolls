<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id: pnadmin.php 91 2008-07-07 19:06:23Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Advanced_Polls
 */

/**
 * Log a vote and display the results form
 *
 * @author Mark West
 * @param pollid the poll to vote on
 * @param voteid the option to vote on
 * @return string updated display for the block
 */
function advanced_polls_ajax_vote()
{
    $pollid = FormUtil::getPassedValue('pollid', null, 'POST');
    $title  = FormUtil::getPassedValue('title', null, 'POST');

    if (!SecurityUtil::checkPermission('advanced_polls::', "$title::$pollid", ACCESS_COMMENT)) {
        AjaxUtil::error(_MODULENOAUTH);
    }

    if (!SecurityUtil::confirmAuthKey()) {
        AjaxUtil::error(_BADAUTHKEY);
    }

    // load the language file
    pnModLangLoad('advanced_polls', 'user');

    // Check the user can vote in this poll
	$isvoteallowed = pnModAPIFunc('advanced_polls',	'user', 'isvoteallowed', array('pollid' => $pollid));

    if ($isvoteallowed) {
        $result = pnModAPIFunc('advanced_polls', 'user', 'vote',
                               array('pollid' => $pollid,
                                     'title' => $title,
                                     'voteid' => $voteid));
    }

    // Get the poll
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    $pnRender = pnRender::getInstance('advanced_polls', false);
    $pnRender->assign($item);
    $pnRender->assign('isvoteallowed', $isvoteallowed);
    // ajax voting is definately on here...
    $pnRender->assign('ajaxvoting', true);

    // Populate block info and pass to theme
    $result = $pnRender->fetch('advancedpolls_block_poll.htm');

    // return the new content for the block
    return array('result' => $result);
}
