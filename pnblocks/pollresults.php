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
function advanced_polls_pollresultsblock_init()
{
    // Security
    SecurityUtil::registerPermissionSchema('advanced_polls:pollresultsblock:', 'Block title::');
}

/**
 * Get information on block
 * @returns block info array
 */
function advanced_polls_pollresultsblock_info()
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Values
    return array('module'         => 'advanced_polls',
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
function advanced_polls_pollresultsblock_display($blockinfo) {

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls:pollresultsblock:',	"$blockinfo[title]::",	ACCESS_READ)) {
        return;
    }

    // get full details on this poll from api
    $pollid = pnModAPIFunc('advanced_polls', 'user', 'getlastclosed');

    //don't show block if no closed polls yet
    if ($pollid == 0) {
        return;
    }

    // get full details on this poll from api
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    // don't show block if we failed to get the item
    if ($item == false) {
        return false;
    }

    // check if we need to reset any poll votes
    $resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring', array('pollid' => $pollid));

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls', false);

    // get current vote counts
    $votecounts = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));

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
function advanced_polls_pollresultsblock_modify($blockinfo)
{
    return;
}

/**
 * Update block settings
 * @returns block info array
 */
function advanced_polls_pollresultsblock_update($blockinfo)
{
    $vars['numitems']     = FormUtil::getPassedValue('numitems');

    // generate the block array
    $blockinfo['content'] = pnBlockVarsToContent($vars);

    return $blockinfo;
}
