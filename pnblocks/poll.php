<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2010 by Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * initialise block
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_pollblock_init()
{
    // Security
    pnSecAddSchema('advanced_polls:pollblock:', 'Block title::');
}

/**
 * get information on block
 * @returns block info array
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_pollblock_info()
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Values
    return array('module'          => 'advanced_polls',
                 'text_type'       => __('Poll', $dom),
                 'text_type_long'  => __('Show a poll', $dom),
                 'allow_multiple'  => true,
                 'form_content'    => false,
                 'form_refresh'    => false,
                 'show_preview'    => true,
                 'admin_tableless' => true);
}

/**
 * display block
 * @returns HTML output or false if no work to do
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_pollblock_display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // set some defaults
    if (!isset($vars['pollid'])) {
        $vars['pollid'] = 1;
    }
    if (!isset($vars['pollopenclosebaseddisplay'])) {
        $vars['pollopenclosebaseddisplay'] = 0;
    }
    if (!isset($vars['polluse'])) {
        $vars['polluse'] = 0;
    }
    if (!isset($vars['polldisplayresults'])) {
        $vars['polldisplayresults'] = 1;
    }
    if (!isset($vars['ajaxvoting'])) {
        $vars['ajaxvoting'] = false;
    }

    // set return url
    $returnurl = 'http://' .pnServerGetVar('HTTP_HOST') . pnServerGetVar('SCRIPT_NAME');

    switch ($vars['polluse']) {
        case 1:
            $items = pnModAPIFunc('advanced_polls', 'user',	'getall', array('startnum' => 0, 'numitems' => 1, 'desc' => true));
            $item = $items[0];
            $pollid = $item['pollid'];
            break;
        case 2:
            $pollid = pnModAPIFunc('advanced_polls', 'user', 'getrandom');
            break;
        default:
            $pollid = $vars['pollid'];
    }

    // get full details on this poll from api
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    // If we've failed to get the item then this pollid doesn't exist or some other problem has
    // occured so we're return no content.
    if ($item == false) {
        return false;
    }

    // check if we need to reset any poll votes
    $resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring',	array('pollid' => $pollid));

    // is this poll currently open for voting
    $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $pollid));

    // if the block is set to obey poll open and closing rules then return no
    // output if the poll is not open for voting
    if (($vars['pollopenclosebaseddisplay']) and ($ispollopen == false)) {
        return false;
    }

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $renderer = pnRender::getInstance('advanced_polls', false);

    // is this user/ip etc. allowed to vote under voting regulations
    $isvoteallowed = pnModAPIFunc('advanced_polls',	'user',	'isvoteallowed', array('pollid' => $pollid));

    // get current vote counts
    $votecounts = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));

    // set leading vote title
    if (isset($item['options'][$votecounts['leadingvoteid']-1])) {
        $votecounts['leadingvotename'] = $item['options'][$votecounts['leadingvoteid']-1]['optiontext'];
    } else {
        $votecounts['leadingvotename'] = '';
    }

    // check for permissions on poll
    if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
        if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_READ)) {

            // if poll is open then display voting form otherwise
            // show results summary
            if (($ispollopen == true) and ($isvoteallowed == true)) {
                $renderer->assign('polltype', $item['multipleselect']);
                $renderer->assign('multiplecount', $item['multipleselectcount']);
            } else {
                foreach ($item['options'] as $key => $option) {
                    if ($option['optiontext']) {
                        if (isset($votecounts['votecountarray'][$key+1])
                        && $votecounts['votecountarray'][$key+1] != 0) {
                            $percent = ($votecounts['votecountarray'][$key+1] / $votecounts['totalvotecount']) * 100;
                        } else {
                            $percent = 0;
                        }
                        $percentages[$key] = array('percent' => (int)$percent,
                                        'percentintscaled' => (int)$percent * 4);
                    }
                }
                $votecounts['percentages'] = $percentages;
            }
        } else {
            return;
        }
    } else {
        return;
    }

    // assign the poll to the template
    $renderer->assign('pollid', $pollid);
    $renderer->assign('item', $item);
    $renderer->assign('votecounts', $votecounts);
    $renderer->assign('isvoteallowed', $isvoteallowed);
    $renderer->assign('ispollopen', $ispollopen);
    $renderer->assign('blockvars', $vars);

    // Populate block info and pass to theme
    $blockinfo['content'] = $renderer->fetch('advancedpolls_block_poll.htm');
    return themesideblock($blockinfo);
}

/**
 * modify block settings
 * @returns pnHMTL object output
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_pollblock_modify($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Get current content
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // Defaults
    if (!isset($vars['pollid'])) {
        $vars['pollid'] = 1;
    }
    if (!isset($vars['pollopenclosebaseddisplay'])) {
        $vars['pollopenclosebaseddisplay'] = 0;
    }
    if (!isset($vars['polluse'])) {
        $vars['polluse'] = 0;
    }
    if (!isset($vars['polldisplayresults'])) {
        $vars['polldisplayresults'] = 1;
    }
    if (!isset($vars['ajaxvoting'])) {
        $vars['ajaxvoting'] = false;
    }

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls', false);

    // get a full list of available polls
    $items = pnModAPIFunc('advanced_polls',	'user',	'getall');
    $polls = array();
    if (is_array($items)) {
        foreach ($items as $item) {
            $polls[$item['pollid']] = $item['title'];
        }
    }
    $renderer->assign('items', $polls);

    // assign the block vars to the template
    $renderer->assign('blockvars', $vars);

    // poll use values
    $renderer->assign('pollusevalues', array( 0 => 'Individual Selection',
    1 => 'Latest',
    2 => 'Random'));

    // yes/no array
    $renderer->assign('yesno', array( 0 => __('No', $dom),
    1 => __('Yes', $dom)));

    // Return output
    return $renderer->fetch('advancedpolls_block_poll_modify.htm');
}

/**
 * update block settings
 * @returns block info array
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_pollblock_update($blockinfo)
{
    $vars['pollid'] = pnVarCleanFromInput('pollid');
    $vars['pollopenclosebaseddisplay'] = pnVarCleanFromInput('pollopenclosebaseddisplay');
    $vars['polluse'] = pnVarCleanFromInput('polluse');
    $vars['polldisplayresults'] = pnVarCleanFromInput('polldisplayresults');
    $vars['ajaxvoting'] = pnVarCleanFromInput('ajaxvoting');
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
