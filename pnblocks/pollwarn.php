<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @author Mats Kling
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Advanced_Polls
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
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_OVERVIEW)) {
		return;
	}
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_READ)) {
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
    $renderer = pnRender::getInstance('advanced_polls');

    // We need the pnsecgenauthkey plugin, so we must not cache here.
    $renderer->caching = false;

	// assign content to the template
	$renderer->assign('blockvars', $vars);
	$renderer->assign('item', $item);

	// Populate block info and pass to theme
	$blockinfo['content'] = $renderer->fetch('advancedpolls_block_pollwarn.htm');
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
	$renderer = pnRender::getInstance('advanced_polls');

    // Assign the block variables
	$renderer->assign('blockvars', $vars);
	
	// assign the items
	$renderer->assign('items', $polls);

	// Return output
	return $renderer->fetch('advancedpolls_block_pollwarn_modify.htm');
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
