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
 * the main user function
 * This function is the default function, and is called whenever the module is
 * initiated without defining arguments.  As such it can be used for a number
 * of things, but most commonly it either just shows the module menu and
 * returns or calls whatever the module designer feels should be the default
 * function (often this is the view() function)
 * @returns HTML output
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_user_main()
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls');

    // Add menu to output - it helps if all of the module pages have a standard
    // menu at their head to aid in navigation
    $renderer->assign('main', advanced_polls_user_view());

    // Return the output that has been generated by this function
    return $renderer->fetch('advancedpolls_user_main.htm');
}

/**
 * view items
 * This is a standard function to provide an overview of all of the items
 * available from the module.
 * @returns HTML output
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_user_view()
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Get parameters from whatever input we need.
    $startnum = FormUtil::getPassedValue('startnum');

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls');

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    // The API function is called.
    $items = pnModAPIFunc('advanced_polls', 'user', 'getall', array('startnum' => $startnum));

    // The return value of the function is checked
    if ($items == false) {
        return LogUtil::registerError(__('Error! No polls found.', $dom));
    }

    //----------------------------------------------------------------------------
    // display polls that are currently active
    //----------------------------------------------------------------------------
    $activepolls = array();
    foreach ($items as $item) {

        $fullitem = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $item['pollid']));

        // is this poll currently open for voting
        $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $fullitem['pollid']));

        // is this user/ip etc. allowed to vote under voting regulations
        $isvoteallowed = pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $fullitem['pollid']));
        if ($fullitem['opendate'] > time()) {
            $notyetopen = true;
        } else {
            $notyetopen = false;
        }

        if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_READ)) {

            if ($ispollopen == true) {
                //and ($isvoteallowed == true))

                $options = array();
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_READ)) {
                    if ($isvoteallowed == true) {
                        $options[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
                                           'image' => 'demo.gif',
                                           'title' => __('Vote', $dom));
                    } else {
                        $options[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'], 'results' => 1)),
                                           'image' => 'smallcal.gif',
                                           'title' => __('Results', $dom));
                    }
                }
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_EDIT)) {
                    $options[] = array('url' => pnModURL('advanced_polls', 'admin', 'modify', array('pollid' => $item['pollid'])),
                                       'image' => 'xedit.gif',
                                       'title' => __('Edit', $dom));
                }

                if ($fullitem['closedate'] == 0) {
                    $closedate = __('No close date', $dom);
                } else {
                    $closedate = DateUtil::formatDatetime($fullitem['closedate'], 'datetimebrief');
                }
                $activepolls[]=  array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
                                       'title' => $item['title'],
                                       'closedate' => $closedate,
                                       'options' => $options);
            }
        }
    }
    $renderer->assign('activepolls', $activepolls);

    //----------------------------------------------------------------------------
    // Output Polls that have not opened yet
    //----------------------------------------------------------------------------
    $futurepolls = array();
    foreach ($items as $item) {

        $fullitem = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $item['pollid']));

        // is this poll currently open for voting
        $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $item['pollid']));

        // is this user/ip etc. allowed to vote under voting regulations
        $isvoteallowed = pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $item['pollid']));

        if ($fullitem['opendate'] > time()) {
            $notyetopen = true;
        } else {
            $notyetopen = false;
        }

        if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_READ)) {

            if (($ispollopen == false) and ($isvoteallowed == true) and ($notyetopen == true)) {

                $options = array();
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_COMMENT)) {
                    $options[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
                                       'image' => '14_layer_visible.gif',
                                       'title' => __('Preview', $dom)); }
                    if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_EDIT)) {
                        $options[] = array('url' => pnModURL('advanced_polls', 'admin', 'modify', array('pollid' => $item['pollid'])),
                                       'image' => 'xedit.gif',
                                       'title' => __('Edit', $dom));
                    }
                    $futurepolls[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
                                           'title' => $item['title'],
                                           'opendate' => DateUtil::formatDatetime($fullitem['opendate'], 'datetimebrief'),
                                           'options' => $options);
            }
        }
    }
    $renderer->assign('futurepolls', $futurepolls);

    //----------------------------------------------------------------------------
    // Output Polls that have closed
    //----------------------------------------------------------------------------
    $closedpolls = array();
    foreach ($items as $item) {

        $fullitem = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $item['pollid']));

        // is this poll currently open for voting
        $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $item['pollid']));

        // is this user/ip etc. allowed to vote under voting regulations
        // show all closed polls in the previous poll list
        // $isvoteallowed = pnModAPIFunc('advanced_polls',
        // 'user',
        // 'isvoteallowed',
        // array('pollid' => $item['pollid']));

        if ($fullitem['opendate'] > time()) {
            $notyetopen = true;
        } else {
            $notyetopen = false;
        }

        // Security check
        if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_READ)) {

            //if (($ispollopen == false) and ($isvoteallowed == true) and ($notyetopen == false)) {
            if (($ispollopen == false) and ($notyetopen == false)) {

                $options = array();
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_COMMENT)) {
                    $options[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
                                       'image' => 'smallcal.gif',
                                       'title' => __('Results', $dom));
                }
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_EDIT)) {
                    $options[] = array('url' => pnModURL('advanced_polls', 'admin', 'modify', array('pollid' => $item['pollid'])),
                                       'image' => 'xedit.gif',
                                       'title' => __('Edit', $dom));
                }
                $closedpolls[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
                                       'title' => $item['title'],
                                       'opendate' => DateUtil::formatDatetime($fullitem['closedate'], 'datetimebrief'),
                                       'options' => $options);
            }
        }
    }
    $renderer->assign('closedpolls', $closedpolls);

    // Return the output that has been generated by this function
    return $renderer->fetch('advancedpolls_user_view.htm');
}

/**
 * display item
 * This is a standard function to provide detailed informtion on a single poll
 * available from the module.
 * @param $args['pollid'] Poll id to display
 * @param $args['results'] 1 to show results components 0 otherwise
 * @returns HTML output
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_user_display($args)
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    $pollid = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'GET');
    $title = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null, 'GET');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // Get the poll
    if (isset($pollid) && is_numeric($pollid)) {
        $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid, 'parse' => true));
    } else {
        $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('title' => $title, 'parse' => true));
        pnQueryStringSetVar('pollid', $item['pollid']);
        $pollid = $item['pollid'];
    }

    if ($item == false) {
        return LogUtil::registerError (__('Error! No such item found.', $dom), 404);
    }

    // Create output object
    $renderer = pnRender::getInstance('advanced_polls');

    // get theme name
    $renderer->assign('theme', pnUserGetTheme());
    $renderer->assign('modvars', pnModGetVar('advanced_polls'));

    // check if we need to reset any poll votes
    $resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring', array('pollid' => $pollid));

    // Security check
    if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_READ)) {

        // is this poll currently open for voting
        $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $item['pollid']));

        // is this user/ip etc. allowed to vote under voting regulations
        $isvoteallowed = pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $item['pollid']));

        // get vote counts
        $votecount = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));

        if ($item['opendate'] > time()) {
            $notyetopen = true;
        } else {
            $notyetopen = false;
        }

        // Now lets work out which view to display
        if ($results) { $displayresults = true; }
        if ($ispollopen && $isvoteallowed) { $displayvotingform = true;}
        if (!$isvoteallowed || (!$ispollopen && !$notyetopen)) { $displayresults = true; }
        if ($notyetopen) { $displaypreview = true; }

        //----------------------------------------------------------------------------
        // if poll is open, voting is allowed then display voting form
        //----------------------------------------------------------------------------
        if ($displayvotingform) {
            $renderer->assign('polltype', $item['multipleselect']);
            $renderer->assign('multiplecount', $item['multipleselectcount']);
            $template = 'advancedpolls_user_votingform';
        } elseif ($displayresults) {
            //----------------------------------------------------------------------------
            // Output results graph if poll has closed/ is not open yet or
            // if results have been spefifically requested
            //----------------------------------------------------------------------------
            $scalingfactor = pnModGetVar('advanced_polls', 'scalingfactor');
            $renderer->assign('ispollopen', $ispollopen);
            $renderer->assign('votecount', $votecount);

            // display poll results
            $pollresults = array();
            foreach ($item['options'] as $key => $option) {
                if ($votecount['votecountarray'][$key+1]  != 0) {
                    $item['options'][$key]['percent'] = ($votecount['votecountarray'][$key+1] / $votecount['totalvotecount']) * 100;
                } else {
                    $item['options'][$key]['percent']= 0;
                }
                $item['options'][$key]['percentint'] = (int)$item['options'][$key]['percent'];
                $item['options'][$key]['percentintscaled'] = $item['options'][$key]['percentint'] * $scalingfactor;
                $item['options'][$key]['votecount'] = $votecount['votecountarray'][$key+1];
            }
            $template = 'advancedpolls_user_results';
        } elseif ($displaypreview) {
            //----------------------------------------------------------------------------
            // Output details of poll if poll is not open yet
            //----------------------------------------------------------------------------
            $template = 'advancedpolls_user_futurepoll';
        } else {
            return LogUtil::registerError(__('Error! No template selected.', $dom));
        }
    } else {
        return LogUtil::registerPermissionError();
    }

    // assign the full poll info
    $renderer->assign('pollid', $pollid);
    $renderer->assign('item', $item);

    // Add template suffix if display function is called via content plugin
    if (isset($args['displaytype'])) {
        $suffix = '_'.$args['displaytype'];
    } else {
        $suffix = '';
    }

    // Return the output that has been generated by this function
    if ($renderer->template_exists($template.$suffix.$pollid.'.htm')) {
        return $renderer->fetch($template.$suffix.$pollid.'.htm');
    } else {
        return $renderer->fetch($template.$suffix.'.htm');
    }
}

/**
 * Process voting form
 * @returns HTML output
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2004 by Mark West
 * @since 1.0
 * @version 1.1
 */
function advanced_polls_user_vote($args)
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // get variables submitted from form
    $pollid             = FormUtil::getPassedValue('pollid');
    $results            = FormUtil::getPassedValue('results');
    $multiple           = FormUtil::getPassedValue('multiple');
    $multiplecount      = FormUtil::getPassedValue('multiplecount');
    $title              = FormUtil::getPassedValue('title');
    $optioncount        = FormUtil::getPassedValue('optioncount');
    $polldisplayresults = FormUtil::getPassedValue('polldisplayresults');
    $returnurl          = FormUtil::getPassedValue('returnurl');
    extract($args);

    // Confirm authorisation code.
    if (!SecurityUtil::confirmAuthKey()) {
        return LogUtil::registerAuthidError (pnModURL('advanced_polls', 'user', 'view'));
    }

    if (!isset($results)) {
        $results = 0;
    }
    if (!isset($multiple)) {
        $multiple = 0;
    }

    if (SecurityUtil::checkPermission('advanced_polls::item',"$title::$pollid",ACCESS_OVERVIEW)) {
        if (SecurityUtil::checkPermission('advanced_polls::item',"$title::$pollid",ACCESS_COMMENT)) {

            // call api function to establish if poll is currently open
            $ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $pollid));

            // if the poll is open then start to add the current vote
            if ($ispollopen == true) {
                // is this vote allowed under voting regulations
                $isvoteallowed = pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $pollid));
                // if vote is allowed then add vote to db tables
                if ($isvoteallowed == true) {
                    if ($multiple == 1) {
                        if ($multiplecount == -1) {
                            $max = $optioncount;
                            for ($i = 1; $i <= $max; $i++) {
                                $optionid = FormUtil::getPassedValue('option' . ($i));
                                if ($optionid != null) {
                                    $result = pnModAPIFunc('advanced_polls', 'user', 'addvote', array('pollid' => $pollid,
                                                                                                      'title' => $title,
                                                                                                      'optionid' => $optionid,
                                                                                                      'voterank' => 1));
                                }
                            }
                        } else {
                            for ($i = 1, $max = $multiplecount; $i <= $max; $i++) {
                                $optionid = FormUtil::getPassedValue('option' . ($i));
                                $result = pnModAPIFunc('advanced_polls', 'user', 'addvote', array('pollid' => $pollid,
                                                                                                  'title' => $title,
                                                                                                  'optionid' => $optionid,
                                                                                                  'voterank' => $i));
                            }
                        }
                    } else {
                        $optionid = FormUtil::getPassedValue('option'.$pollid);
                        $result = pnModAPIFunc('advanced_polls', 'user', 'addvote', array('pollid' => $pollid,
                                                                                          'title' => $title,
                                                                                          'optionid' => $optionid,
                                                                                          'voterank' => 1));
                    }
                }
            }
        }
    }

    LogUtil::registerStatus( __('Done! Vote added.', $dom));

    if (($polldisplayresults == 0) && isset($polldisplayresults) && isset($returnurl)) {
        return pnRedirect($returnurl);
    } else {
        return pnRedirect(pnModURL('advanced_polls','user','display', array('pollid' => $pollid, 'results' => $results)));
    }
}
