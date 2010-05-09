<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk>
 * @author Mats Kling
 * @copyright (C) 2002-2010 by Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * initialise block
 */
function advanced_polls_pollwarnblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('advanced_polls:pollblock:', 'Block title::');
}

/**
 * get information on block
 */
function advanced_polls_pollwarnblock_info()
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Values
    return array('module'         => 'advanced_polls',
                 'text_type'      => __('Poll warn', $dom),
                 'text_type_long' => __('Warns if poll is unanswered', $dom),
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true,
                 'admin_tableless' => true);
}

/**
 * display block
 */
function advanced_polls_pollwarnblock_display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // set some defaults
    if (empty($vars['pollid'])) {
        $vars['pollid'] = 1;
    }

    if (empty($vars['pollopenclosebaseddisplay'])) {
        $vars['pollopenclosebaseddisplay'] = 0;
    }

    if (!isset($vars['polluse'])) {
        $vars['polluse'] = 0;
    }

    //extract poll variables from block variables
    $pollopenclosebaseddisplay = $vars['pollopenclosebaseddisplay'];

    switch ($vars['polluse']) {
        case 1:
            $items = pnModAPIFunc('advanced_polls', 'user', 'getall', array('startnum' => 0, 'numitems' => 1, 'desc' => true));
            $item = $items[0];
            $pollid = $item['pollid'];
            break;
        case 2:
            $pollid = pnModAPIFunc('advanced_polls', 'user', 'getrandom');
            break;
        default:
            $pollid = $vars['pollid'];
    }

    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid,
                                                                'titlename' => 'name',
                                                                'idname' => 'id'));
    if ($item == false) {
        return false;
    }

    // check for permissions on poll
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
        return;
    }
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_READ)) {
        return;
    }

    // populate the options array
    $polloptionarray = array();
    $polloptionarray = $item['optionarray'];

    // check if we need to reset any poll votes
    $resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring', array('pollid' => $pollid));

    // is this poll currently open for voting
    $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $pollid));

    // check if the poll is open for voting
    if (($vars['pollopenclosebaseddisplay']) and ($ispollopen == false)) {
        return false;
    }

    // is this user/ip etc. allowed to vote under voting regulations
    $isvoteallowed = pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $pollid));

    // check if the person can vote on this poll
    if ((!$ispollopen == true) and (!$isvoteallowed == true)) {
        return;
    }

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $renderer = pnRender::getInstance('advanced_polls', false);

    // assign content to the template
    $renderer->assign('blockvars', $vars);

    // poll use values
    $renderer->assign('pollusevalues', array( 0 => 'Individual Selection',
    1 => 'Latest',
    2 => 'Random'));

    $renderer->assign('item', $item);

    // Populate block info and pass to theme
    $blockinfo['content'] = $renderer->fetch('advancedpolls_block_pollwarn.htm');
    return themesideblock($blockinfo);
}

/**
 * modify block settings
 */
function advanced_polls_pollwarnblock_modify($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (empty($vars['pollid'])) {
        $vars['pollid'] = 1;
    }

    if (empty($vars['pollopenclosebaseddisplay'])) {
        $vars['pollopenclosebaseddisplay'] = 0;
    }

    if (empty($vars['polluse'])) {
        $vars['polluse'] = 0;
    }

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls', false);

    // get a full list of available polls
    $items = pnModAPIFunc('advanced_polls', 'user', 'getall');
    $polls = array();
    if (is_array($items)) {
        foreach ($items as $item) {
            $polls[$item['pollid']] = $item['title'];
        }
    }
    $renderer->assign('items', $polls);

    // Assign the block variables
    $renderer->assign('blockvars', $vars);

    // poll use values
    $renderer->assign('pollusevalues', array( 0 => 'Individual Selection',
    1 => 'Latest',
    2 => 'Random'));

    // yes/no array
    $renderer->assign('yesno', array( 0 => __('No', $dom),
    1 => __('Yes', $dom)));

    // Return output
    return $renderer->fetch('advancedpolls_block_pollwarn_modify.htm');
}

/**
 * update block settings
 */
function advanced_polls_pollwarnblock_update($blockinfo)
{
    // get the input
    $vars['pollid']                    = FormUtil::getPassedValue('pollid');
    $vars['pollopenclosebaseddisplay'] = FormUtil::getPassedValue('pollopenclosebaseddisplay');
    $vars['polluse']                   = FormUtil::getPassedValue('polluse');
    $vars['backgroundcolor']           = FormUtil::getPassedValue('backgroundcolor');

    // generate the block array
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
