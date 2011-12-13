<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class AdvancedPolls_Block_Poll extends Zikula_Controller_AbstractBlock {
/**
 * Initialise block
 */
public function init()
{
    // Security
    SecurityUtil::registerPermissionSchema('AdvancedPolls:pollblock:', 'Block title::');
}

/**
 * get information on block
 * @returns block info array
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @since 1.0
 * @version 1.1
 */
public function info()
{
    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    // Values
    return array('module'          => 'AdvancedPolls',
                 'text_type'       => __('Poll', $dom),
                 'text_type_long'  => __('Show a poll', $dom),
                 'allow_multiple'  => true,
                 'form_content'    => false,
                 'form_refresh'    => false,
                 'show_preview'    => true,
                 'admin_tableless' => true);
}

/**
 * Display block
 * @returns HTML output or false if no work to do
 */
public function display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    // Get variables from content block
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

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
    $returnurl = 'http://' . System::serverGetVar('HTTP_HOST') . System::serverGetVar('SCRIPT_NAME');

    switch ($vars['polluse']) {
        case 1:
            $items = ModUtil::apiFunc($this->name, 'user', 'getall', array('startnum' => 0, 'numitems' => 1, 'desc' => true));
            $item = $items[0];
            $pollid = $item['pollid'];
            break;
        case 2:
            $pollid = ModUtil::apiFunc($this->name, 'user', 'getrandom');
            break;
        default:
            $pollid = $vars['pollid'];
    }

    // get full details on this poll from api
    $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

    // If we've failed to get the item then this pollid doesn't exist or some other problem has
    // occured so we're return no content.
    if ($item == false) {
        return false;
    }

    // check if we need to reset any poll votes
    $resetrecurring = ModUtil::apiFunc($this->name, 'user', 'resetrecurring', array('pollid' => $pollid));

    // is this poll currently open for voting
    $ispollopen = ModUtil::apiFunc($this->name, 'user', 'isopen', array('pollid' => $pollid));

    // if the block is set to obey poll open and closing rules then return no
    // output if the poll is not open for voting
    if (($vars['pollopenclosebaseddisplay']) and ($ispollopen == false)) {
        return false;
    }

    // is this user/ip etc. allowed to vote under voting regulations
    $isvoteallowed = ModUtil::apiFunc($this->name, 'user', 'isvoteallowed', array('pollid' => $pollid));

    // get current vote counts
    $votecounts = ModUtil::apiFunc($this->name, 'user', 'pollvotecount', array('pollid' => $pollid));

    // set leading vote title
    if (isset($item['options'][$votecounts['leadingvoteid']-1])) {
        $votecounts['leadingvotename'] = $item['options'][$votecounts['leadingvoteid']-1]['optiontext'];
    } else {
        $votecounts['leadingvotename'] = '';
    }

    // check for permissions on poll
    if (SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
        if (SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_READ)) {

            // if poll is open then display voting form otherwise
            // show results summary
            if (($ispollopen == true) and ($isvoteallowed == true)) {
                $this->view->assign('polltype', $item['multipleselect']);
                $this->view->assign('multiplecount', $item['multipleselectcount']);
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
    $this->view->assign('pollid', $pollid);
    $this->view->assign('item', $item);
    $this->view->assign('votecounts', $votecounts);
    $this->view->assign('isvoteallowed', $isvoteallowed);
    $this->view->assign('ispollopen', $ispollopen);
    $this->view->assign('blockvars', $vars);

    // Populate block info and pass to theme
    $blockinfo['content'] = $this->view->fetch('advancedpolls_block_poll.htm');
    return BlockUtil::themesideblock($blockinfo);
}

/**
 * Modify block settings
 * @returns pnHMTL object output
 */
public function modify($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    // Get current content
    $vars = BlockUtil::varsFromContent($blockinfo['content']);

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

    // get a full list of available polls
    $items = ModUtil::apiFunc($this->name, 'user', 'getall');
    $polls = array();
    if (is_array($items)) {
        foreach ($items as $item) {
            $polls[$item['pollid']] = $item['title'];
        }
    }
    $this->view->assign('items', $polls);

    // assign the block vars to the template
    $this->view->assign('blockvars', $vars);

    // poll use values
    $this->view->assign('pollusevalues', array(0 => __('Individual Selection', $dom),
                                             1 => __('Latest', $dom),
                                             2 => __('Random', $dom)));

    // yes/no array
    $this->view->assign('yesno', array(0 => __('No', $dom),
                                     1 => __('Yes', $dom)));

    // Return output
    return $this->view->fetch('advancedpolls_block_poll_modify.htm');
}

/**
 * Update block settings
 * @returns block info array
 */
public function update($blockinfo)
{
    $vars['pollid']                    = FormUtil::getPassedValue('pollid');
    $vars['pollopenclosebaseddisplay'] = FormUtil::getPassedValue('pollopenclosebaseddisplay');
    $vars['polluse']                   = FormUtil::getPassedValue('polluse');
    $vars['polldisplayresults']        = FormUtil::getPassedValue('polldisplayresults');
    $vars['ajaxvoting']                = FormUtil::getPassedValue('ajaxvoting');

    // generate the block array
    $blockinfo['content']              = BlockUtil::varsToContent($vars);

    return $blockinfo;
}
}