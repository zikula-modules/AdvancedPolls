<?php
// $Id$
// ----------------------------------------------------------------------
// Advanced Polls Modile for the POST-NUKE Content Management System
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
// Purpose of file: Display a list of current polls user is allowed
//                  to vote in
// ----------------------------------------------------------------------

/**
* Advanced Polls Module Poll List Block
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
function advanced_polls_polllistblock_init() 
{
	// Security
	pnSecAddSchema('advanced_polls:polllistblock:', 'Block title::');
}

/**
* get information on block
* @returns block info array
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_polllistblock_info() 
{
	// Values
	return array('text_type' => 'Polllist',
		'module' => 'advanced_polls',
		'text_type_long' => 'Display list of Open Polls',
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
function advanced_polls_polllistblock_display($blockinfo) 
{
	// Security check
	if (!pnSecAuthAction(0,	'advanced_polls:polllistblock:', "$blockinfo[title]::", 	ACCESS_READ)) {
		return;
	}
	// Get variables from content block
	$vars = pnBlockVarsFromContent($blockinfo['content']);

    // get a polls from the api
	$items = pnModAPIFunc('advanced_polls', 'user', 'getall');

	// load the user language file
	pnModLangLoad('advanced_polls', 'user');

    // Create output object
    $pnRender =& new pnRender('advanced_polls');

    // We need the pnsecgenauthkey plugin, so we must not cache here.
    $pnRender->caching = false;

    // if there are no polls then don't display anything
	if (count($items) == 0) {
		return;
	}

	// get the currant time
	$currentdate = time();

    // create a results array
	$polls = array();
	foreach ($items as $item) {
		if (pnSecAuthAction(0, 'advanced_polls::item', "$item[polltitle]::$item[pollid]", ACCESS_COMMENT)) {
			// is this user/ip etc. allowed to vote under voting regulations
			if (($currentdate >= $item['opendate'] && $currentdate <= $item['closedate']) || $item['closedate'] == 0) {
				if (pnModAPIFunc('advanced_polls', 'user', 'isvoteallowed', array('pollid' => $item['pollid']))) {
				    $polls[] = $item;
				}
			}
		}
	}
    // if there are no polls then don't display anything
	if (count($polls) == 0) {
		return;
	}

	$pnRender->assign('polls', $polls);

	// Populate block info and pass to theme
	$blockinfo['content'] = $pnRender->fetch('advancedpolls_block_polllist.htm');
	return themesideblock($blockinfo);
}

/**
* modify block settings
* @returns HTML output or false if no work to do
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_polllistblock_modify($blockinfo) 
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
function advanced_polls_polllistblock_update($blockinfo) 
{
	$vars['pollid'] = pnVarCleanFromInput('pollid');
	$blockinfo['content'] = pnBlockVarsToContent($vars);

	return $blockinfo;

}

?>