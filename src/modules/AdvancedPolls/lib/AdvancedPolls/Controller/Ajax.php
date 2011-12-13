<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class AdvancedPolls_Controller_Ajax extends Zikula_Controller_AbstractAjax {

/**
 * Log a vote and display the results form
 *
 * @param pollid the poll to vote on
 * @param voteid the option to vote on
 * @return string updated display for the block
 */
public function vote()
{
    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    $pollid        = FormUtil::getPassedValue('pollid', null, 'POST');
    $title         = FormUtil::getPassedValue('title', null, 'POST');
    $results       = FormUtil::getPassedValue('results', null, 'POST');
    $multiple      = FormUtil::getPassedValue('multiple', null, 'POST');
    $multiplecount = FormUtil::getPassedValue('multiplecount', null, 'POST');
    $optioncount   = FormUtil::getPassedValue('optioncount', null, 'POST');

    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$title::$pollid", ACCESS_COMMENT)) {
        AjaxUtil::error(__('Error! No authorization to access this module.', $dom));
    }

    if (!SecurityUtil::generateCsrfToken()) {
        AjaxUtil::error(__('Invalid \'CsrfToken\': This probably means that you pressed the \'Back\' button, or that the page \'Authkey\' expired. Please refresh the page and try again.', $dom));
    }

    // call api function to establish if poll is currently open
    $ispollopen = ModUtil::apiFunc($this->name, 'user', 'isopen', array('pollid' => $pollid));

    // if the poll is open then start to add the current vote
    if ($ispollopen == true) {
        // is this vote allowed under voting regulations
        $isvoteallowed = ModUtil::apiFunc($this->name, 'user', 'isvoteallowed', array('pollid' => $pollid));
        // if vote is allowed then add vote to db tables
        if ($isvoteallowed == true) {
            if ($multiple == 1) {
                if ($multiplecount == -1) {
                    $max = $optioncount;
                    for ($i = 1; $i <= $max; $i++) {
                        $optionid = FormUtil::getPassedValue('option' . ($i), null, 'POST');
                        if ($optionid != null) {
                            $result = ModUtil::apiFunc($this->name, 'user', 'addvote',
                            array('pollid'   => $pollid,
                                  'title'    => $title,
                                  'optionid' => $optionid,
                                  'voterank' => 1));
                        }
                    }
                } else {
                    for ($i = 1, $max = $multiplecount; $i <= $max; $i++) {
                        $optionid = FormUtil::getPassedValue('option' . ($i), null, 'POST');
                        $result = ModUtil::apiFunc($this->name,'user','addvote',
                        array('pollid'   => $pollid,
                              'title'    => $title,
                              'optionid' => $optionid,
                              'voterank' => $i));
                    }
                }
            } else {
                $optionid = FormUtil::getPassedValue('option'.$pollid, null, 'POST');
                $result = ModUtil::apiFunc($this->name,'user','addvote',
                array('pollid'   => $pollid,
                      'title'    => $title,
                      'optionid' => $optionid,
                      'voterank' => 1));
            }
        }
    }

    // Get the poll
    $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

    // get current vote counts
    $votecounts = ModUtil::apiFunc($this->name, 'user', 'pollvotecount', array('pollid' => $pollid));

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

    // Create output object
    $renderer = pnRender::getInstance($this->name, false);
	
    // assign the item to template
    $renderer->assign('item', $item);
    $renderer->assign('votecounts', $votecounts);

    // ajax voting is definately on here...
    $renderer->assign('ajaxvoting', true);

    // Populate block info and pass to theme
    $result = $renderer->fetch('advancedpolls_block_pollresults.htm');

    // return the new content for the block
    return array('result' => $result);
}
}