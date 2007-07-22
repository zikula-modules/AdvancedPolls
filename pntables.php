<?php
/**
 * Advanced Polls module for PostNuke
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_3rdParty_Modules
 * @subpackage Advanced_Polls
 */

/**
* This function is called internally by the core whenever the module is
* loaded.  It adds in the information
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_pntables() 
{
	// Initialise table array
	$pntable = array();
	 
	//    ------------------
	// Name for poll votes database entities
	$advpolls = pnConfigGetVar('prefix') . '_advanced_polls_votes';
	 
	// Table name
	$pntable['advancedpollsvotes'] = $advpolls;
	 
	// Column names
	$pntable['advanced_polls_votes'] = array('pn_ip' => $advpolls . '.pn_ip',
											 'pn_time' => $advpolls . '.pn_time',
											 'pn_uid' => $advpolls . '.pn_uid',
											 'pn_voterank' => $advpolls . '.pn_voterank',
											 'pn_pollid' => $advpolls . '.pn_pollid',
											 'pn_optionid' => $advpolls . '.pn_optionid',
											 'pn_voteid' => $advpolls . '.pn_voteid');
	//    ------------------
	// Name for poll options database entities
	$advpolls = pnConfigGetVar('prefix') . '_advanced_polls_data';
	 
	// Table name
	$pntable['advancedpollsdata'] = $advpolls;
	 
	// Column names
	$pntable['advanced_polls_data'] = array('pn_pollid' => $advpolls . '.pn_pollid',
											'pn_optiontext' => $advpolls . '.pn_optiontext',
											'pn_optionid' => $advpolls . '.pn_optionid',
											'pn_optioncolour' => $advpolls . '.pn_optioncolour');
	//    ------------------
	// Name for poll description database entities
	$advpolls = pnConfigGetVar('prefix') . '_advanced_polls_desc';
	 
	// Table name
	$pntable['advancedpollsdesc'] = $advpolls;
	 
	// Column names
	$pntable['advanced_polls_desc'] = array('pn_pollid' => $advpolls . '.pn_pollid',
											'pn_title' => $advpolls . '.pn_title',
											'pn_description' => $advpolls . '.pn_description',
											'pn_optioncount' => $advpolls . '.pn_optioncount',
											'pn_opendate' => $advpolls . '.pn_opendate',
											'pn_closedate' => $advpolls . '.pn_closedate',
											'pn_recurring' => $advpolls . '.pn_recurring',
											'pn_recurringoffset' => $advpolls . '.pn_recurringoffset',
											'pn_recurringinterval' => $advpolls . '.pn_recurringinterval',
											'pn_multipleselect' => $advpolls . '.pn_multipleselect',
											'pn_multipleselectcount' => $advpolls . '.pn_multipleselectcount',
											'pn_voteauthtype' => $advpolls . '.pn_voteauthtype',
											'pn_tiebreakalg' => $advpolls . '.pn_tiebreakalg',
											'pn_language' => $advpolls . '.pn_language',
											'pn_votingmethod' => $advpolls . '.pn_votingmethod');
	//    ------------------
	 
	// Return the table information
	return $pntable;
}

