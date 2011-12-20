<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */
class AdvancedPolls_Block_Polllist extends Zikula_Controller_AbstractBlock {
/**
 * Initialise block
 */
public function init()
{
    // Security
    SecurityUtil::registerPermissionSchema('AdvancedPolls:polllistblock:', 'Block title::');
}

/**
 * Get information on block
 * @returns block info array
 */
public function info()
{
    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    // Values
    return array('module'         => 'AdvancedPolls',
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
public function display($blockinfo)
{
    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls:polllistblock:', "$blockinfo[title]::", 	ACCESS_READ)) {
        return;
    }
    // Get variables from content block
    $vars = pnBlockVarsFromContent($blockinfo['content']);

    // get a polls from the api
    $items = ModUtil::apiFunc('AdvancedPolls', 'user', 'getall');

    // Create output object
    $renderer = pnRender::getInstance('AdvancedPolls', false);

    // if there are no polls then don't display anything
    if (count($items) == 0) {
        return;
    }

    // get the currant time
    $currentdate = time();

    // create a results array
    $polls = array();
    foreach ($items as $item) {
        if (SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$item[pollid]", ACCESS_COMMENT)) {
            // is this user/ip etc. allowed to vote under voting regulations
            if (($currentdate >= $item['opendate'] && $currentdate <= $item['closedate']) || $item['closedate'] == 0) {
                if (ModUtil::apiFunc('AdvancedPolls', 'user', 'isvoteallowed', array('pollid' => $item['pollid']))) {
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
    $blockinfo['content'] = $renderer->fetch('block/polllist.tpl');
    return themesideblock($blockinfo);
}

/**
 * Modify block settings
 * @returns HTML output or false if no work to do
 */
public function modify($blockinfo)
{
    return;
}

/**
 * Update block settings
 * @returns block info array
 */
public function update($blockinfo)
{
    $vars['pollid']       = FormUtil::getPassedValue('pollid');

    // generate the block array
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;

}
}