<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Advanced_Polls
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
	$pnRender = pnRender::getInstance('advanced_polls');

	// Add menu to output - it helps if all of the module pages have a standard
	// menu at their head to aid in navigation
	$pnRender->assign('main', advanced_polls_user_view());

	// Return the output that has been generated by this function
	return $pnRender->fetch('advancedpolls_user_main.htm');
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
	// Get parameters from whatever input we need.
	$startnum = pnVarCleanFromInput('startnum');

    // Create output object
	$pnRender = pnRender::getInstance('advanced_polls');

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
		return LogUtil::registerPermissionError();
	}

	// The API function is called.
	$items = pnModAPIFunc('advanced_polls',	'user',	'getall', array('startnum' => $startnum));

	// The return value of the function is checked
	if ($items == false) {
		return pnVarPrepHTMLDisplay(_ADVANCEDPOLLSITEMFAILED);
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
		$isvoteallowed = pnModAPIFunc('advanced_polls',	'user', 'isvoteallowed', array('pollid' => $fullitem['pollid']));

		if ($fullitem['opendate'] > time()) {
			$notyetopen = true;
		} else {
			$notyetopen = false;
		}

		if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_READ)) {

			if ($ispollopen == true) {
				//and ($isvoteallowed == true))
									 
				$options = array();
				if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_COMMENT)) {
					if ($isvoteallowed == true) {
						$options[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
										   'title' => _ADVANCEDPOLLSVOTE);
					} else {
						$options[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'], 'results' => 1)),
							               'title' => _ADVANCEDPOLLSRESULTS);
					}
				}
				if (SecurityUtil::checkPermission('advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_EDIT)) {
					$options[] = array('url' => pnModURL('advanced_polls', 'admin', 'modify', array('pollid' => $item['pollid'])),
									   'title' => _EDIT);
				}

				if ($fullitem['closedate'] == 0) {
					$closedate = _ADVANCEDPOLLSNOCLOSEDATE;
				} else {
					$closedate = ml_ftime(constant(pnModGetVar('advanced_polls', 'userdateformat')), $fullitem['closedate']);
				}
				$activepolls[]=  array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
					                   'title' => $item['title'],
									   'closedate' => $closedate,
									   'options' => $options);
			}
		}
	}
    $pnRender->assign('activepolls', $activepolls);
	//----------------------------------------------------------------------------
	// Output Polls that have not opened yet
	//----------------------------------------------------------------------------
    $futurepolls = array();
	foreach ($items as $item) {

		$fullitem = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $item['pollid']));

		// is this poll currently open for voting
		$ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $item['pollid']));

		// is this user/ip etc. allowed to vote under voting regulations
		$isvoteallowed = pnModAPIFunc('advanced_polls',	'user',	'isvoteallowed', array('pollid' => $item['pollid']));

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
						               'title' => _ADVANCEDPOLLSPREVIEW);
				}
				$futurepolls[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
					                   'title' => $item['polltitle'],
								       'opendate' => ml_ftime(constant(pnModGetVar('advanced_polls', 'userdateformat')), $fullitem['opendate']),
									   'options' => $options);
			}
		}
	}
    $pnRender->assign('futurepolls', $futurepolls);

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
		//$isvoteallowed = pnModAPIFunc('advanced_polls',
		//	'user',
		//	'isvoteallowed',
		//	array('pollid' => $item['pollid']));

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
						               'title' => _ADVANCEDPOLLSRESULTS);
				}
                $closedpolls[] = array('url' => pnModURL('advanced_polls', 'user', 'display', array('pollid' => $item['pollid'])),
					                   'title' => $item['polltitle'],
									   'opendate' => ml_ftime(constant(pnModGetVar('advanced_polls', 'userdateformat')), $fullitem['opendate']),
									   'options' => $options);
			}
		}
	}
    $pnRender->assign('closedpolls', $closedpolls);

	// Return the output that has been generated by this function
	return $pnRender->fetch('advancedpolls_user_view.htm');
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
	// Get parameters from whatever input we need.
	$pollid = pnVarCleanFromInput('pollid');
	$results = pnVarCleanFromInput('results');
	extract($args);

    // Create output object
	$pnRender = pnRender::getInstance('advanced_polls');

	// get theme name
	$pnRender->assign('theme', pnUserGetTheme());

	// The API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// The return value of the function is checked
	if ($item == false) {
		return pnVarPrepHTMLDisplay(_ADVANCEDPOLLSITEMFAILED);
	}

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
			$pnRender->assign('polltype', $item['multipleselect']);
			$pnRender->assign('multiplecount', $item['multipleselectcount']);
			$template = 'advancedpolls_user_votingform';
		} elseif ($displayresults)  {
			//----------------------------------------------------------------------------
			// Output results graph if poll has closed/ is not open yet or
			// if results have been spefifically requested
			//----------------------------------------------------------------------------
			$pnRender->assign('ispollopen', $ispollopen);

			// display poll results
			$pollresults = array();
			foreach ($item['options'] as $key => $option) {
				if ($votecount['votecountarray'][$key+1]  != 0) {
					$item['options'][$key]['percent'] = ($votecount['votecountarray'][$key+1] / $votecount['totalvotecount']) * 100;
				} else {
					$item['options'][$key]['percent']= 0;
				}
				$item['options'][$key]['percentint'] = (int)$item['options'][$key]['percent'];
				$item['options'][$key]['percentintscaled'] = $$item['options'][$key]['percentint'] * pnModGetVar('advanced_polls', 'scalingfactor');
				$item['options'][$key]['votecount'] = $votecount['votecountarray'][$key+1];
			}
			$template = 'advancedpolls_user_results';
		} elseif ($displaypreview) {
			//----------------------------------------------------------------------------
			// Output details of poll if poll is not open yet
			//----------------------------------------------------------------------------
			$votecount = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));
	
			// display poll results
			$options = array();
			for ($i = 0, $max = count($polloptionarray); $i < $max; $i++) {
				$options[] = array('optiontext' => $polloptionarray[$i]['name']);
			}
			$pnRender->assign('options', $options);
	
			// close results table out
			$template = 'advancedpolls_user_futurepoll';
		} else {
			return pnVarPrepHTMLDisplay(_ADVANCEDPOLLSNOTEMPLATESELECTED);
		}
	} else {
		return LogUtil::registerPermissionError();
	}

    // assign the full poll info
	$pnRender->assign('pollid', $pollid);
	$pnRender->assign('item', $item);

    // Let any hooks know that we are displaying an item.
    $pnRender->assign('hooks' ,pnModCallHooks('item',
                                              'display',
                                              $pollid,
                                              pnModURL('advanced_polls',
                                                       'user',
                                                       'display',
                                                       array('pollid' => $pollid))));

	// Return the output that has been generated by this function
	if ($pnRender->template_exists($template.$pollid.'.htm')) {
		return $pnRender->fetch($template.$pollid.'.htm');
	} else {
		return $pnRender->fetch($template.'.htm');
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
	// get variables submitted from form
	$pollid = pnVarCleanFromInput('pollid');
	$results = pnVarCleanFromInput('results');
	$multiple = pnVarCleanFromInput('multiple');
	$multiplecount = pnVarCleanFromInput('multiplecount');
	$title = pnVarCleanFromInput('title');
	$optioncount =  pnVarCleanFromInput('optioncount');
	$polldisplayresults = pnVarCleanFromInput('polldisplayresults');
	$returnurl = pnVarCleanFromInput('returnurl');
	extract($args); 

	if (!isset($results)) {
		$results = 0;
	}
	if (!isset($multiple)) {
		$multiple = 0;
	}

	if (pnSecAuthAction(0,'advanced_polls::item',"$title::$pollid",ACCESS_OVERVIEW)) {
	    if (pnSecAuthAction(0,'advanced_polls::item',"$title::$pollid",ACCESS_COMMENT)) {

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
								$optionid = pnVarCleanFromInput('option' . ($i));
								if ($optionid != null) {
									$result = pnModAPIFunc('advanced_polls', 'user', 'addvote',
															array('pollid' => $pollid,
																  'title' => $title,
																  'optionid' => $optionid,
																  'voterank' => 1));
								}
							 }
						} else {
							for ($i = 1, $max = $multiplecount; $i <= $max; $i++) {
								$optionid = pnVarCleanFromInput('option' . ($i));
								$result = pnModAPIFunc('advanced_polls','user','addvote',
									array('pollid' => $pollid,
										  'title' => $title,
										  'optionid' => $optionid,
										  'voterank' => $i));
							}
						}
					} else {
						$optionid = pnVarCleanFromInput('option'.$pollid);
						$result = pnModAPIFunc('advanced_polls','user','addvote',
							array('pollid' => $pollid,
								  'title' => $title,
								  'optionid' => $optionid,
								  'voterank' => 1));
					}
				}
			}
		}
	}

	if (($polldisplayresults == 0) && isset($polldisplayresults) && isset($returnurl)) {
		return pnRedirect($returnurl);
	} else {
		return pnRedirect(pnModURL('advanced_polls','user','display',
							 array('pollid' => $pollid,
								   'results' => $results)));
	}
}

