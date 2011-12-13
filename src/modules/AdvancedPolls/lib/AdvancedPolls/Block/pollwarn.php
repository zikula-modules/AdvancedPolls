<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */
class AdvancedPolls_Block_Pollwarn extends Zikula_Controller_AbstractBlock {
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
 */
public function info()
{
    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    // Values
    return array('module'         => 'AdvancedPolls',
                 'text_type'      => __('Poll warn', $dom),
                 'text_type_long' => __('Warns if poll is unanswered', $dom),
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true,
                 'admin_tableless' => true);
}

/**
 * Display block
 */
public function display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

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
            $items = ModUtil::apiFunc('AdvancedPolls', 'user', 'getall', array('startnum' => 0, 'numitems' => 1, 'desc' => true));
            $item = $items[0];
            $pollid = $item['pollid'];
            break;
        case 2:
            $pollid = ModUtil::apiFunc('AdvancedPolls', 'user', 'getrandom');
            break;
        default:
            $pollid = $vars['pollid'];
    }

    $item = ModUtil::apiFunc('AdvancedPolls', 'user', 'get', array('pollid' => $pollid,
                                                                'titlename' => 'name',
                                                                'idname' => 'id'));
    if ($item == false) {
        return false;
    }

    // check for permissions on poll
    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
        return;
    }
    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_READ)) {
        return;
    }

    // populate the options array
    $polloptionarray = array();
    $polloptionarray = $item['optionarray'];

    // check if we need to reset any poll votes
    $resetrecurring = ModUtil::apiFunc('AdvancedPolls', 'user', 'resetrecurring', array('pollid' => $pollid));

    // is this poll currently open for voting
    $ispollopen = ModUtil::apiFunc('AdvancedPolls', 'user', 'isopen', array('pollid' => $pollid));

    // check if the poll is open for voting
    if (($vars['pollopenclosebaseddisplay']) and ($ispollopen == false)) {
        return false;
    }

    // is this user/ip etc. allowed to vote under voting regulations
    $isvoteallowed = ModUtil::apiFunc('AdvancedPolls', 'user', 'isvoteallowed', array('pollid' => $pollid));

    // check if the person can vote on this poll
    if ((!$ispollopen == true) and (!$isvoteallowed == true)) {
        return;
    }

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $renderer = pnRender::getInstance('AdvancedPolls', false);

    // assign content to the template
    $renderer->assign('blockvars', $vars);

    // poll use values
    $renderer->assign('pollusevalues', array(0 => __('Individual Selection', $dom),
                                             1 => __('Latest', $dom),
                                             2 => __('Random', $dom)));

    $renderer->assign('item', $item);

    // Populate block info and pass to theme
    $blockinfo['content'] = $renderer->fetch('advancedpolls_block_pollwarn.htm');
    return themesideblock($blockinfo);
}

/**
 * Modify block settings
 */
public function modify($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

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
    $renderer = pnRender::getInstance('AdvancedPolls', false);

    // get a full list of available polls
    $items = ModUtil::apiFunc('AdvancedPolls', 'user', 'getall');
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
    $renderer->assign('pollusevalues', array(0 => __('Individual Selection', $dom),
                                             1 => __('Latest', $dom),
                                             2 => __('Random', $dom)));

    // yes/no array
    $renderer->assign('yesno', array(0 => __('No', $dom),
                                     1 => __('Yes', $dom)));

    // Return output
    return $renderer->fetch('advancedpolls_block_pollwarn_modify.htm');
}

/**
 * Update block settings
 */
public function update($blockinfo)
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
}