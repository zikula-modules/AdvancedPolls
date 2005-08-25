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
// Purpose of file: Advanced Polls Table Definitions
// ----------------------------------------------------------------------
 
/**
* Advanced Polls Module Table Definitions
* @package Advanced_Polls
* @version $Id$
* @author Mark West <mark@markwest.me.uk> 
* @link http://www.markwest.me.uk Advanced Polls Support Site
* @copyright (C) 2002-2004 by Mark West
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

?>