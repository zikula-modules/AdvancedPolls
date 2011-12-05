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

class AdvancedPolls_Controller_User extends Zikula_AbstractController {

/**
 * the main user function
 * This function is the default function, and is called whenever the module is
 * initiated without defining arguments.  As such it can be used for a number
 * of things, but most commonly it either just shows the module menu and
 * returns or calls whatever the module designer feels should be the default
 * function (often this is the view() function)
 * @returns HTML output
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @since 1.0
 * @version 1.1
 */
public function main($args)
{
    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    // do nothing
    return System::redirect(ModUtil::url($this->name, 'user', 'view'));
}

/**
 * view items
 * This is a standard function to provide an overview of all of the items
 * available from the module.
 * @returns HTML output
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @since 1.0
 * @version 1.1
 */
public function view($args)
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    // Get parameters from whatever input we need.
    $startnum = (int)FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GETPOST');
    $property = FormUtil::getPassedValue('advanced_polls_property', isset($args['advanced_polls_property']) ? $args['advanced_polls_property'] : null, 'GETPOST');
    $category = FormUtil::getPassedValue("advanced_polls_{$property}_category", isset($args["advanced_polls_{$property}_category"]) ? $args["advanced_polls_{$property}_category"] : null, 'GETPOST');
    $clear    = FormUtil::getPassedValue('clear', false, 'POST');
    if ($clear) {
        $property = null;
        $category = null;
    }

    // get module vars for later use
    $modvars = ModUtil::getVar('advanced_polls');

    if ($modvars['enablecategorization']) {
        // load the category registry util
        if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s]', array('s' => 'CategoryRegistryUtil'), $dom));
        }
        $catregistry  = CategoryRegistryUtil::getRegisteredModuleCategories('advanced_polls', 'advanced_polls_desc');
        $properties = array_keys($catregistry);

        // Validate and build the category filter - mateo
        if (!empty($property) && in_array($property, $properties) && !empty($category)) {
            $catFilter = array($property => $category);
        }

        // Assign a default property - mateo
        if (empty($property) || !in_array($property, $properties)) {
            $property = $properties[0];
        }

        // plan ahead for ML features
        $propArray = array();
        foreach ($properties as $prop) {
            $propArray[$prop] = $prop;
        }
    }

    // Create output object
    //$renderer = pnRender::getInstance('advanced_polls');
    $this->view->assign($modvars);
    $this->view->assign('lang', ZLanguage::getLanguageCode());

    // Assign the categories information if enabled
    if ($modvars['enablecategorization']) {
        $this->view->assign('catregistry', $catregistry);
        $this->view->assign('numproperties', count($propArray));
        $this->view->assign('properties', $propArray);
        $this->view->assign('property', $property);
        $this->view->assign('category', $category);
    }

    // get all matching polls
    $items = ModUtil::apiFunc($this->name, 'user', 'getall', array('checkml' => false,
                                                                    'startnum' => $startnum,
                                                                    'category' => isset($catFilter) ? $catFilter : null,
                                                                    'catregistry'  => isset($catregistry) ? $catregistry : null));

    // The return value of the function is checked
    if ($items == false) {
        return LogUtil::registerError(__('Error! No polls found.', $dom));
    }

    $activepolls = array();
    $futurepolls = array();
    $closedpolls = array();

    foreach ($items as $item) {

        // is this poll currently open for voting
        $ispollopen = ModUtil::apiFunc($this->name, 'user', 'isopen', array('pollid' => $item['pollid']));

        // is this user/ip etc. allowed to vote under voting regulations
        $isvoteallowed = ModUtil::apiFunc($this->name, 'user', 'isvoteallowed', array('pollid' => $item['pollid']));

        if ($item['opendate'] > time()) {
            $notyetopen = true;
        } else {
            $notyetopen = false;
        }

        $item['isopen'] = $ispollopen;
        $item['isvoteallowed'] = $isvoteallowed;
        $item['notyetopen'] = $notyetopen;
        $options = array();

        if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_READ)) {
            if ($ispollopen == true) {
                // display polls that are currently active
                if ($isvoteallowed == true) {
                    $options[] = array('url' => ModUtil::url($this->name, 'user', 'display', array('pollid' => $item['pollid'])),
                                       'image' => 'demo.png',
                                       'title' => __('Vote', $dom));
                } else {
                    $options[] = array('url' => ModUtil::url($this->name, 'user', 'display', array('pollid' => $item['pollid'], 'results' => 1)),
                                       'image' => 'vcalendar.png',
                                       'title' => __('Results', $dom));
                }
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_EDIT)) {
                    $options[] = array('url' => ModUtil::url($this->name, 'admin', 'modify', array('pollid' => $item['pollid'])),
                                       'image' => 'xedit.png',
                                       'title' => __('Edit', $dom));
                }

                $item['options'] = $options;
                $activepolls[] = $item;
            } elseif (($ispollopen == false) and ($notyetopen == true)) {
                // Polls that have not opened yet
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_COMMENT)) {
                    $options[] = array('url' => ModUtil::url($this->name, 'user', 'display', array('pollid' => $item['pollid'])),
                                       'image' => '14_layer_visible.png',
                                       'title' => __('Preview', $dom)); }
                    if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_EDIT)) {
                        $options[] = array('url' => ModUtil::url($this->name, 'admin', 'modify', array('pollid' => $item['pollid'])),
                                       'image' => 'xedit.png',
                                       'title' => __('Edit', $dom));
                    }
                    $item['options'] = $options;
                    $futurepolls[] = $item;
            } elseif (($ispollopen == false) and ($notyetopen == false)) {
                // Polls that have closed
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_COMMENT)) {
                    $options[] = array('url' => ModUtil::url($this->name, 'user', 'display', array('pollid' => $item['pollid'])),
                                       'image' => 'vcalendar.png',
                                       'title' => __('Results', $dom));
                }
                if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_EDIT)) {
                    $options[] = array('url' => ModUtil::url($this->name, 'admin', 'modify', array('pollid' => $item['pollid'])),
                                       'image' => 'xedit.png',
                                       'title' => __('Edit', $dom));
                }
                $item['options'] = $options;
                $closedpolls[] = $item;
            }
        }
    }

    $this->view->assign('activepolls', $activepolls);
    $this->view->assign('futurepolls', $futurepolls);
    $this->view->assign('closedpolls', $closedpolls);

    // Return the output that has been generated by this function
    return $this->view->fetch('advancedpolls_user_view.htm');
}

/**
 * display item
 * This is a standard function to provide detailed informtion on a single poll
 * available from the module.
 * @param $args['pollid'] Poll id to display
 * @param $args['results'] 1 to show results components 0 otherwise
 * @returns HTML output
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @since 1.0
 * @version 1.1
 */
public function display($args)
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    $pollid   = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'GET');
    $title    = FormUtil::getPassedValue('title', isset($args['title']) ? $args['title'] : null, 'GET');
    $results  = FormUtil::getPassedValue('results', isset($args['results']) ? $args['results'] : null, 'GET');
    $objectid = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'GET');
    if (!empty($objectid)) {
        $pollid = $objectid;
    }

    // get module vars for later use
    $modvars = ModUtil::getVar('advanced_polls');

    // Get the poll
    if (isset($pollid) && is_numeric($pollid)) {
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid, 'parse' => true));
    } else {
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('title' => $title, 'parse' => true));
        System::queryStringSetVar('pollid', $item['pollid']);
        $pollid = $item['pollid'];
    }

    if ($item == false) {
        return LogUtil::registerError ($this->__('Error! No such poll found.'), 404);
    }

    // get theme name
    $this->view->assign('theme', UserUtil::getTheme());
    $this->view->assign($modvars);
    $this->view->assign('lang', ZLanguage::getLanguageCode());

    // check if we need to reset any poll votes
    $resetrecurring = ModUtil::apiFunc($this->name, 'user', 'resetrecurring', array('pollid' => $pollid));

    // Security check
    if (SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$item[pollid]", ACCESS_READ)) {

        // is this poll currently open for voting
        $ispollopen = ModUtil::apiFunc($this->name, 'user', 'isopen', array('pollid' => $item['pollid']));

        // is this user/ip etc. allowed to vote under voting regulations
        $isvoteallowed = ModUtil::apiFunc($this->name, 'user', 'isvoteallowed', array('pollid' => $item['pollid']));

        // get vote counts
        $votecount = ModUtil::apiFunc($this->name, 'user', 'pollvotecount', array('pollid' => $pollid));

        if ($item['opendate'] > time()) {
            $notyetopen = true;
        } else {
            $notyetopen = false;
        }

        $displayvotingform = false;
        $displayresults = false;
        // Now lets work out which view to display
        if ($results) { $displayresults = true; }
        if ($ispollopen && $isvoteallowed) { $displayvotingform = true;}
        if (!$isvoteallowed || (!$ispollopen && !$notyetopen)) { $displayresults = true; }
        if ($notyetopen) { $displaypreview = true; }

        //----------------------------------------------------------------------------
        // if poll is open, voting is allowed then display voting form
        //----------------------------------------------------------------------------
        if ($displayvotingform) {
            $this->view->assign('polltype', $item['multipleselect']);
            $this->view->assign('multiplecount', $item['multipleselectcount']);
            $template = 'advancedpolls_user_votingform';
        } elseif ($displayresults) {
            //----------------------------------------------------------------------------
            // Output results graph if poll has closed/ is not open yet or
            // if results have been spefifically requested
            //----------------------------------------------------------------------------
            $scalingfactor = ModUtil::getVar('advanced_polls', 'scalingfactor');
            $this->view->assign('ispollopen', $ispollopen);
            $this->view->assign('votecount', $votecount);

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
    $this->view->assign('pollid', $pollid);
    $this->view->assign('item', $item);

    // Add template suffix if display function is called via content plugin
    if (isset($args['displaytype'])) {
        $suffix = '_'.$args['displaytype'];
    } else {
        $suffix = '';
    }

    // Return the output that has been generated by this function
    if ($this->view->template_exists($template.$suffix.$pollid.'.htm')) {
        return $this->view->fetch($template.$suffix.$pollid.'.htm');
    } else {
        return $this->view->fetch($template.$suffix.'.htm');
    }
}

/**
 * Process voting form
 * @returns HTML output
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @since 1.0
 * @version 1.1
 */
public function vote($args)
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
    if (!SecurityUtil::generateCsrfToken()) {
        return LogUtil::registerPermissionError (ModUtil::url($this->name, 'user', 'view'));
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
                                $optionid = FormUtil::getPassedValue('option' . ($i));
                                if ($optionid != null) {
                                    $result = ModUtil::apiFunc($this->name, 'user', 'addvote', array('pollid' => $pollid,
                                                                                                      'title' => $title,
                                                                                                      'optionid' => $optionid,
                                                                                                      'voterank' => 1));
                                }
                            }
                        } else {
                            for ($i = 1, $max = $multiplecount; $i <= $max; $i++) {
                                $optionid = FormUtil::getPassedValue('option' . ($i));
                                $result = ModUtil::apiFunc($this->name, 'user', 'addvote', array('pollid' => $pollid,
                                                                                                  'title' => $title,
                                                                                                  'optionid' => $optionid,
                                                                                                  'voterank' => $i));
                            }
                        }
                    } else {
                        $optionid = FormUtil::getPassedValue('option'.$pollid);
                        $result = ModUtil::apiFunc($this->name, 'user', 'addvote', array('pollid' => $pollid,
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
        return System::redirect($returnurl);
    } else {
        return System::redirect(ModUtil::url($this->name, 'user', 'display', array('pollid' => $pollid, 'results' => $results)));
    }
}
}