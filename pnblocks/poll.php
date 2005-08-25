<?php
// $Id$
// ----------------------------------------------------------------------
// Advanced Polls Module for the POST-NUKE Content Management System
// Copyright (C) 2002-2004 by Mark West
// http://www.markwest.me.uk/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Mark West
// Purpose of file: Display a Poll
// ----------------------------------------------------------------------

/**
* Advanced Polls Module Poll Display Block
* @package Advanced_Polls
* @version $Id$
* @author Mark West <mark@markwest.me.uk> 
* @link http://www.markwest.me.uk Advanced Polls Support Site
* @copyright (C) 2002-2004 by Mark West
*/
 
/**
* initialise block
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollblock_init() 
{
	// Security
	pnSecAddSchema('advanced_polls:pollblock:', 'Block title::');
}
 
/**
* get information on block
* @returns block info array
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollblock_info() 
{
	// Values
	return array('text_type' => 'Poll',
		'module' => 'advanced_polls',
		'text_type_long' => 'Show a Poll',
		'allow_multiple' => true,
		'form_content' => false,
		'form_refresh' => false,
		'show_preview' => true);
}

/**
* display block
* @returns HTML output or false if no work to do
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollblock_display($blockinfo) 
{
	// Security check
	if (!pnSecAuthAction(0,	'advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
		return;
	}

	// Get variables from content block
	$vars = pnBlockVarsFromContent($blockinfo['content']);

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

	//extract poll variables from block variables
	$pollid = $vars['pollid'];
	$pollopenclosebaseddisplay = $vars['pollopenclosebaseddisplay'];
	$polluse = $vars['polluse'];
	$polldisplayresults = $vars['polldisplayresults'];

	// set return url
	$returnurl = 'http://' .pnServerGetVar('HTTP_HOST') . pnServerGetVar('SCRIPT_NAME');

	// Load API.  All of the actual work for obtaining information on the items
	// is done within the API, so we need to load that in before we can do
	// anything.  If the API fails to load an appropriate error message is
	// posted and the function returns
	if (!pnModAPILoad('advanced_polls', 'user')) {
		return pnVarPrepHTMLDisplay(_LOADFAILED);
	}

	if ($polluse == 1) {
		$items = pnModAPIFunc('advanced_polls', 'user',	'getall', array('startnum' => 0, 'numitems' => 1, 'desc' => true));
		$item = $items[0];
		$pollid = $item['pollid'];
	}

	if ($polluse == 2) {
		$pollid = pnModAPIFunc('advanced_polls', 'user', 'getrandom');
	}

	// get full details on this poll from api
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    // If we've failed to get the item then this pollid doesn't exist or some other problem has 
	// occured so we're return no content.
	if ($item == false) {
		return false;
	}

	$polloptionarray = array();
	$polloptionarray = $item['pn_optionarray'];

	// check if we need to reset any poll votes
	$resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring',	array('pollid' => $pollid));

	// is this poll currently open for voting
	$ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $pollid));

    // if the block is set to obey poll open and closing rules then return no 
	// output if the poll is not open for voting
	if (($vars['pollopenclosebaseddisplay']) and ($ispollopen == false)) {
		return false;
	}

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnRender =& new pnRender('advanced_polls');

    // We need the pnsecgenauthkey plugin, so we must not cache here.
    $pnRender->caching = false;

	// is this user/ip etc. allowed to vote under voting regulations
	$isvoteallowed = pnModAPIFunc('advanced_polls',	'user',	'isvoteallowed', array('pollid' => $pollid));

	// get current vote counts
	$votecounts = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));

	// set leading vote title
	if (isset($polloptionarray[$votecounts['pn_leadingvoteid']-1])) {
		$votecounts['leadingvotename'] = $polloptionarray[$votecounts['pn_leadingvoteid']-1]['optiontext'];
	} else {
		$votecounts['leadingvotename'] = '';
	}

	// check for permissions on poll
	if (pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_OVERVIEW)) {
		if (pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_READ)) {

			// if poll is open then display voting form otherwise
			// show results summary
			if (($ispollopen == true) and ($isvoteallowed == true)) {
				$pnRender->assign('polltype', $item['pn_multipleselect']);
				$pnRender->assign('multiplecount', $item['pn_multipleselectcount']);
				$options = array();
				for ($i = 0, $max = count($polloptionarray); $i < $max; $i++) {
					$optiontext = $polloptionarray[$i]['optiontext'];
					$optioncolor = $polloptionarray[$i]['optioncolour'];
					$voteid = $polloptionarray[$i]['voteid'];
					if ($optiontext) {
						$options[] = array('optiontext' => $optiontext,
										   'optioncolor' => $optioncolor,
										   'voteid' => $voteid);
					}
				}
				$pnRender->assign('options', $options);
			} else {
				for ($i = 1, $max = count($polloptionarray); $i <= $max; $i++) {
					$optionText = $polloptionarray[$i-1]['optiontext'];
					if ($optionText) {
						if (isset($votecounts['pn_votecountarray'][$i])
						    && $votecounts['pn_votecountarray'][$i]  != 0) {
							$percent = ($votecounts['pn_votecountarray'][$i] / $votecounts['pn_totalvotecount']) * 100;
						} else {
							$percent = 0;
						}
						$percentages[$i-1] = array('percent' => (int)$percent,
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
	$pnRender->assign('pollid', $pollid);
	$pnRender->assign('item', $item);
	$pnRender->assign('polloptions', $polloptionarray);
	$pnRender->assign('votecounts', $votecounts);
	$pnRender->assign('isvoteallowed', $isvoteallowed);
    $pnRender->assign('ispollopen', $ispollopen);
	$pnRender->assign('blockvars', $vars);
 
	// Populate block info and pass to theme
	$blockinfo['content'] = $pnRender->fetch('poll.htm');
	return themesideblock($blockinfo);
}

/**
* modify block settings
* @returns pnHMTL object output
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollblock_modify($blockinfo) 
{
	// Security check
	if (!pnSecAuthAction(0,	'advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
		return;
	}

	// Get current content
	$vars = pnBlockVarsFromContent($blockinfo['content']);

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

	// Load API.  All of the actual work for obtaining information on the items
	// is done within the API, so we need to load that in before we can do
	// anything.  If the API fails to load an appropriate error message is
	// posted and the function returns
	if (!pnModAPILoad('advanced_polls', 'user')) {
		return pnVarPrepHTMLDisplay(_LOADFAILED);
	}

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnRender =& new pnRender('advanced_polls');

    // We need the pnsecgenauthkey plugin, so we must not cache here.
    $pnRender->caching = false;

    // get a full list of available polls
	$items = pnModAPIFunc('advanced_polls',	'user',	'getall');
	$polls = array();
	if (is_array($items)) {
		foreach ($items as $item) {
		    $polls[$item['pollid']] = $item['polltitle'];
		}
	}
	$pnRender->assign('items', $polls);

	// assign the block vars to the template
    $pnRender->assign('blockvars', $vars);

	// poll use values
	$pnRender->assign('pollusevalues', array( 0 => 'Individual Selection',
					                          1 => 'Latest',
										      2 => 'Random'));

	// yes/no array
	$pnRender->assign('yesno', array( 0 => _NO,
									  1 => _YES));

	// Return output
	return $pnRender->fetch('pollmodify.htm');
}

/**
* update block settings
* @returns block info array
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollblock_update($blockinfo) 
{
	$vars['pollid'] = pnVarCleanFromInput('pollid');
	$vars['pollopenclosebaseddisplay'] = pnVarCleanFromInput('pollopenclosebaseddisplay');
	$vars['polluse'] = pnVarCleanFromInput('polluse');
	$vars['polldisplayresults'] = pnVarCleanFromInput('polldisplayresults');
	$blockinfo['content'] = pnBlockVarsToContent($vars);

	return $blockinfo;
}

?>