<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */
class AdvancedPolls_Block_Pollresults extends Zikula_Controller_AbstractBlock {
/**
 * Initialise block
 */
public function init()
{
    // Security
    SecurityUtil::registerPermissionSchema('AdvancedPolls:pollresultsblock:', 'Block title::');
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
                 'text_type'      => __('Poll results', $dom),
                 'text_type_long' => __('Show results of the most recently closed poll', $dom),
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true,
                 'admin_tableless' => true);
}

/**
 * Display block
 * @returns HTML output
 * @returns HTML output or false if no work to do
 */
public function display($blockinfo) {

    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls:pollresultsblock:',	"$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    // get full details on this poll from api
    $pollid = pnModAPIFunc('AdvancedPolls', 'user', 'getlastclosed');

    //don't show block if no closed polls yet
    if ($pollid == 0) {
        return;
    }

    // get full details on this poll from api
    $item = pnModAPIFunc('AdvancedPolls', 'user', 'get', array('pollid' => $pollid));

    // don't show block if we failed to get the item
    if ($item == false) {
        return false;
    }

    // check if we need to reset any poll votes
    $resetrecurring = pnModAPIFunc('AdvancedPolls', 'user', 'resetrecurring', array('pollid' => $pollid));

    // Create output object
    $renderer = pnRender::getInstance('AdvancedPolls', false);

    // get current vote counts
    $votecounts = pnModAPIFunc('AdvancedPolls', 'user', 'pollvotecount', array('pollid' => $pollid));

    // don't show block if we failed to get any results
    if ($votecounts == false) {
        return false;
    }

    // set leading vote title
    if (isset($item['options'][$votecounts['leadingvoteid']-1])) {
        $votecounts['leadingvotename'] = $item['options'][$votecounts['leadingvoteid']-1]['optiontext'];
    } else {
        $votecounts['leadingvotename'] = '';
    }

    // calculate results of poll
    $percentages = array();
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

    // assign the item to template
    $renderer->assign('item', $item);
    $renderer->assign('votecounts', $votecounts);

    // Populate block info and pass to theme
    $blockinfo['content'] = $renderer->fetch('advancedpolls_block_pollresults.htm');
    return themesideblock($blockinfo);
}

/**
 * Update block settings
 * @returns HTML object
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
    $vars['numitems']     = FormUtil::getPassedValue('numitems');

    // generate the block array
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
}