<?php
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
// Modification by: Mats Kling
// Purpose of file: Fits together with Advanced Polls by Mark West
//                  It shows a warning is the poll is unasvered by UID
// ----------------------------------------------------------------------

/**
* Advanced Polls Module User Frontend
* @package Advanced_Polls
* @version $Id$
* @author Mats Kling
* @link http://www.markwest.me.uk Advanced Polls Support Site
*/
 
/**
* initialise block
*/
function advanced_polls_pollwarnblock_init() 
{
	// Security
	pnSecAddSchema('advanced_polls:pollblock:', 'Block title::');
}
 
/**
* get information on block
*/
function advanced_polls_pollwarnblock_info() 
{
	// Values
	return array('text_type' => 'Pollwarn',
				'module' => 'advanced_polls',
				'text_type_long' => 'Warns if Poll is unanswered',
				'allow_multiple' => true,
				'form_content' => false,
				'form_refresh' => false,
				'show_preview' => true);
}

/**
* display block
*/
function advanced_polls_pollwarnblock_display($blockinfo) 
{

	// Security check
	if (!pnSecAuthAction(0,	'advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
		return;
	}

	// Get variables from content block
	$vars = pnBlockVarsFromContent($blockinfo['content']);

    // set some defaults
	if (empty($vars['pollid'])) {
		$vars['pollid'] = 1;
	}

	if (empty($vars['pollopenclosebaseddisplay'])) {
		$vars['pollopenclosebaseddisplay'] = 0;
	}

	if (empty($vars['polluse'])) {
		$vars['polluse'] = 0;
	}

	//extract poll variables from block variables
	$pollid = $vars['pollid'];
	$pollopenclosebaseddisplay = $vars['pollopenclosebaseddisplay'];
	$polluse = $vars['polluse'];

	// Load API.  All of the actual work for obtaining information on the items
	// is done within the API, so we need to load that in before we can do
	// anything.  If the API fails to load an appropriate error message is
	// posted and the function returns
	if (!pnModAPILoad('advanced_polls', 'user')) {
		return pnVarPrepHTMLDisplay(_LOADFAILED);
	}

	if ($polluse == 1) {
		$items = pnModAPIFunc('advanced_polls',	'user',	'getall', array('startnum' => 0));
		$item = $items[0];
		$pollid = $item['pollid'];
	}

	if ($polluse == 2) {
		$pollid = pnModAPIFunc('advanced_polls', 'user', 'getrandom');
		//$pollid = $item['pn_pollid'];
	}

	// get full details on this poll from api
	$item = pnModAPIFunc('advanced_polls',	'user',	'get',	array('pollid' => $pollid,
														          'titlename' => 'name',
																  'idname' => 'id'));
	if ($item == false) {
		return false;
	}

	// check for permissions on poll
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_OVERVIEW)) {
		return;
	}
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_READ)) {
		return;			
	}

    // populate the options array
	$polloptionarray = array();
	$polloptionarray = $item['pn_optionarray'];

	// check if we need to reset any poll votes
	$resetrecurring = pnModAPIFunc('advanced_polls', 'user', 'resetrecurring', array('pollid' => $pollid));

	// is this poll currently open for voting
	$ispollopen = pnModAPIFunc('advanced_polls', 'user', 'isopen', array('pollid' => $pollid));

    // check if the poll is open for voting
	if (($vars['pollopenclosebaseddisplay']) and ($ispollopen == false)) {
		return false;
	}

	// is this user/ip etc. allowed to vote under voting regulations
	$isvoteallowed = pnModAPIFunc('advanced_polls',	'user',	'isvoteallowed', array('pollid' => $pollid));

    // check if the person can vote on this poll
	if ((!$ispollopen == true) and (!$isvoteallowed == true)) {
		return;
	}

    // Create output object - this object will store all of our output so that
    // we can return it easily when required
    $pnRender =& new pnRender('advanced_polls');

    // We need the pnsecgenauthkey plugin, so we must not cache here.
    $pnRender->caching = false;

	// assign content to the template
	$pnRender->assign('blockvars', $vars);
	$pnRender->assign('item', $item);

	// Populate block info and pass to theme
	$blockinfo['content'] = $pnRender->fetch('pollwarn.htm');
	return themesideblock($blockinfo);
}

/**
* modify block settings
*/
function advanced_polls_pollwarnblock_modify($blockinfo) 
{
	// Security check
	if (!pnSecAuthAction(0,	'advanced_polls:pollblock:', "$blockinfo[title]::",	ACCESS_READ)) {
		return;
	}

	// Get current content
	$vars = pnBlockVarsFromContent($blockinfo['content']);

	// Defaults
	if (empty($vars['pollid'])) {
		$vars['pollid'] = 1;
	}

	if (empty($vars['pollopenclosebaseddisplay'])) {
		$vars['pollopenclosebaseddisplay'] = 0;
	}

	if (empty($vars['polluse'])) {
		$vars['polluse'] = 0;
	}

	// Load API.  All of the actual work for obtaining information on the items
	// is done within the API, so we need to load that in before we can do
	// anything.  If the API fails to load an appropriate error message is
	// posted and the function returns
	if (!pnModAPILoad('advanced_polls', 'user')) {
		return pnVarPrepHTMLDisplay(_LOADFAILED);
	}

    //  get all items from the module
	$items = pnModAPIFunc('advanced_polls',	'user',	'getall');
	$polls = array();
	if (is_array($items)) {
    	foreach ($items as $item) {
	    	$polls[$item['pollid']] = $item['polltitle'];
		}
	}

	// Create output object - this object will store all of our output so that
	// we can return it easily when required
	$pnRender =& new pnRender('advanced_polls');

    // Assign the block variables
	$pnRender->assign('blockvars', $vars);
	
	// assign the items
	$pnRender->assign('items', $polls);

	// Return output
	return $pnRender->fetch('pollwarnmodify.htm');
}

/**
* update block settings
*/
function advanced_polls_pollwarnblock_update($blockinfo) 
{
    // get the input
	$vars['pollid'] = pnVarCleanFromInput('pollid');
	$vars['pollopenclosebaseddisplay'] = pnVarCleanFromInput('pollopenclosebaseddisplay');
	$vars['polluse'] = pnVarCleanFromInput('polluse');
	$vars['backgroundcolor'] = pnVarCleanFromInput('backgroundcolor');

	// generate the block array
	$blockinfo['content'] = pnBlockVarsToContent($vars);

	return $blockinfo;
}

?>