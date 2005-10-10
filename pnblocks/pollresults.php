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
// Purpose of file: Display the results of the most recently closed Poll
// ----------------------------------------------------------------------

/**
* Advanced Polls Module Poll List Block
* @package Advanced_Polls
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
function advanced_polls_pollresultsblock_init() 
{
	// Security
	pnSecAddSchema('advanced_polls:pollresultsblock:', 'Block title::');
}
 
/**
* get information on block
* @returns block info array
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollresultsblock_info() 
{
	// Values
	return array('text_type' => 'Poll',
				'module' => 'advanced_polls',
				'text_type_long' => 'Show Results of the Most Recently Closed Poll',
				'allow_multiple' => true,
				'form_content' => false,
				'form_refresh' => false,
				'show_preview' => true);
}
 
/**
* display block
* @returns HTML output
* @returns HTML output or false if no work to do
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollresultsblock_display($blockinfo) {
	 
	// Security check
	if (!pnSecAuthAction(0,	'advanced_polls:pollresultsblock:',	"$blockinfo[title]::",	ACCESS_READ)) {
		return;
	}

	// Load API.  All of the actual work for obtaining information on the items
	// is done within the API, so we need to load that in before we can do
	// anything.  If the API fails to load an appropriate error message is
	// posted and the function returns
	if (!pnModAPILoad('advanced_polls', 'user')) {
		return pnVarPrepHTMLDisplay(_LOADFAILED);
	}

	// get full details on this poll from api
	$pollid = pnModAPIFunc('advanced_polls', 'user', 'getlastclosed');
	
	//don't show block if no closed polls yet
	if ($pollid == 0) {
		return;
	}

	// get full details on this poll from api
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid, 'titlename' => 'name', 'idname' => 'voteid'));

    // don't show block if we failed to get the item
	if ($item == false) {
		return false;
	}

    // create an array from the poll options for ease of reference
	$polloptionarray = array();
	$polloptionarray = $item['pn_optionarray'];
	 
	// check if we need to reset any poll votes
	$resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring', array('pollid' => $pollid));
	 
    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnRender =& new pnRender('advanced_polls');

    // We need the pnsecgenauthkey plugin, so we must not cache here.
    $pnRender->caching = false;

	// get current vote counts
	$votecounts = pnModAPIFunc('advanced_polls', 'user', 'pollvotecount', array('pollid' => $pollid));

    // calculate results of poll
	$percentages = array();
	for ($i = 1, $max = count($polloptionarray); $i <= $max; $i++) {
		$optionText = $polloptionarray[$i-1]['optiontext'];
		if ($optionText) {
			if ($votecounts['pn_votecountarray'][$i]  != 0) {
				$percent = ($votecounts['pn_votecountarray'][$i] / $votecounts['pn_totalvotecount']) * 100;
			} else {
				$percent = 0;
			}
			$percentages[$i-1] = array('percent' => (int)$percent,
									   'percentintscaled' => (int)$percent * 4);
		}
	}
	$votecounts['percentages'] = $percentages;

	// assign the item to template
	$pnRender->assign('item', $item);
	$pnRender->assign('polloptions', $polloptionarray);
	$pnRender->assign('votecounts', $votecounts);

	// Populate block info and pass to theme
	$blockinfo['content'] = $pnRender->fetch('advancedpolls_block_pollresults.htm');
	return themesideblock($blockinfo);
}

/**
* update block settings
* @returns HTML object
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollresultsblock_modify($blockinfo) 
{
	return;
}

/**
* update block settings
* @returns block info array
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pollresultsblock_update($blockinfo) 
{
	$vars['numitems'] = pnVarCleanFromInput('numitems');

	$blockinfo['content'] = pnBlockVarsToContent($vars);

	return $blockinfo;
}

?>