<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West, Carsten Volmer
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Initialise block
 */
function advanced_polls_polllistblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('advanced_polls:polllistblock:', 'Block title::');
}

/**
 * Get information on block
 * @returns block info array
 */
function advanced_polls_polllistblock_info()
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Values
    return array('module'         => 'advanced_polls',
                 'text_type'      => __('Poll list', $dom),
                 'text_type_long' => __('Display list of open polls', $dom),
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true,
                 'admin_tableless' => true);
}

/**
 * Display block
 * @returns HTML output or false if no work to do
 */
function advanced_polls_polllistblock_display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls:polllistblock:', "$blockinfo[title]::", 	ACCESS_READ)) {
        return;
    }
    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // get a polls from the api
    $items = pnModAPIFunc('advanced_polls', 'user', 'getall');

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls', false);

    // if there are no polls then don't display anything
    if (count($items) == 0) {
        return;
    }

    // get the currant time
    $currentdate = time();

    // create a results array
    $polls = array();
    foreach ($items as $item) {
        if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_COMMENT)) {
            // is this user/ip etc. allowed to vote under voting regulations
            if (($currentdate >= $item['opendate'] && $currentdate <= $item['closedate']) || $item['closedate'] == 0) {
                if (pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $item['pollid']))) {
                    $polls[] = $item;
                }
            }
        }
    }
    // if there are no polls then don't display anything
    if (count($polls) == 0) {
        return;
    }

    $renderer->assign('polls', $polls);

    // Populate block info and pass to theme
    $blockinfo['content'] = $renderer->fetch('advancedpolls_block_polllist.htm');
    return themesideblock($blockinfo);
}

/**
 * Modify block settings
 * @returns HTML output or false if no work to do
 */
function advanced_polls_polllistblock_modify($blockinfo)
{
    return;
}

/**
 * Update block settings
 * @returns block info array
 */
function advanced_polls_polllistblock_update($blockinfo)
{
    $vars['pollid']       = FormUtil::getPassedValue('pollid');

    // generate the block array
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;

}
