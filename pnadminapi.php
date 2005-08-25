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
// Purpose of file: Advanced Polls Administration API
// ----------------------------------------------------------------------

/**
* Advanced Polls Module Administration API
* @package Advanced_Polls
* @version $Id$
* @author Mark West <mark@markwest.me.uk> 
* @link http://www.markwest.me.uk Advanced Polls Support Site
* @copyright (C) 2002-2004 by Mark West
*/
 
/**
* Create a new Poll item
* @param $args['pollname'] the name of the poll to be created
* @param $args['polldescription'] the description of the poll to be created
* @param $args['polllanguage'] the number of the item to be created
* @param $args['pollopendid'] the day component of the poll open date
* @param $args['pollopenmid'] the month component of the poll open date
* @param $args['pollopenyid'] the year component of the poll open date
* @param $args['pollopenhid'] the hour component of the poll open time
* @param $args['pollopenminid'] the minute component of the poll open time
* @param $args['pollclosedid'] the day component of the poll close date
* @param $args['pollclosemid'] the month component of the poll close date
* @param $args['pollcloseyid'] the year component of the poll close date
* @param $args['pollclosehid'] the hour component of the poll close time
* @param $args['pollcloseminid'] the minute component of the poll close time
* @param $args['polltiebreak'] the tiebreak methodlogy to use
* @param $args['pollvoteauthtype'] vote authorisation type to use
* @param $args['multipleselect'] type of poll selection
* @param $args['multipleselectcount'] number of selections allowed
* @param $args['pollrecurring'] is poll a recurring one
* @param $args['pollreucrringoffset'] offset for recurring polls
* @param $args['pollrecurringinterval'] interval to add for recurring polls
* @param $args['polloptioncount'] number of options for this poll
* @returns int
* @return poll item ID on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_adminapi_create($args) 
{
	// Get arguments from argument array - all arguments to this function
	// should be obtained from the $args array, getting them from other
	// places such as the environment is not allowed, as that makes
	// assumptions that will not hold in future versions of PostNuke
	extract($args);

	// Argument check - make sure that all required arguments are present,
	// if not then set an appropriate error message and return
	if ((!isset($pollname)) || 
		(!isset($polldescription)) ||
		(!isset($polllanguage)) ||
		(!isset($pollopendid)) ||
		(!isset($pollopenmid)) ||
		(!isset($pollopenyid)) || 
		(!isset($pollopenhid)) ||
		(!isset($pollopenminid)) ||
		(!isset($pollclosedid)) || 
		(!isset($pollclosemid)) || 
		(!isset($pollcloseyid)) || 
		(!isset($pollclosehid)) ||
		(!isset($pollcloseminid)) ||
		(!isset($pollvoteauthtype)) ||
		(!isset($polltiebreak)) ||
		(!isset($pollmultipleselect)) ||
		(!isset($pollmultipleselectcount)) ||
		(!isset($pollrecurring)) ||
		(!isset($pollrecurringinterval)) ||
		(!isset($pollrecurringoffset)) ||
		(!isset($polloptioncount))) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSVARIABLEERROR);
		return false;
	}

	// Security check - important to do this as early on as possible to
	// avoid potential security holes or just too much wasted processing
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$pollname::", ACCESS_ADD)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}
	 
	// Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
	// return arrays but we handle them differently.  For pnDBGetConn()
	// we currently just want the first item, which is the official
	// database handle.  For pnDBGetTables() we want to keep the entire
	// tables array together for easy reference later on
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	// It's good practice to name the table and column definitions you
	// are getting - $table and $column don't cut it in more complex
	// modules
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];

	// Get next ID in table - this is required prior to any insert that
	// uses a unique ID, and ensures that the ID generation is carried
	// out in a database-portable fashion
	$nextId = $dbconn->GenId($advanced_pollsdesctable);

	$pollopendate = mktime ($pollopenhid, $pollopenminid, 0, $pollopenmid, $pollopendid, $pollopenyid);
	if (!$pollnoclosedate) {
		$pollclosedate = mktime ($pollclosehid, $pollcloseminid, 0, $pollclosemid, $pollclosedid, $pollcloseyid);
	} else {
	 	$pollclosedate = 0;
	}

	// Add item - the formatting here is not mandatory, but it does make
	// the SQL statement relatively easy to read.  Also, separating out
	// the sql statement from the Execute() command allows for simpler
	// debug operation if it is ever needed
	$sql = "INSERT INTO $advanced_pollsdesctable (
		$advanced_pollsdesccolumn[pn_pollid],
		$advanced_pollsdesccolumn[pn_title],
		$advanced_pollsdesccolumn[pn_description],
		$advanced_pollsdesccolumn[pn_optioncount],
		$advanced_pollsdesccolumn[pn_opendate],
		$advanced_pollsdesccolumn[pn_closedate],
		$advanced_pollsdesccolumn[pn_voteauthtype],
		$advanced_pollsdesccolumn[pn_tiebreakalg],
		$advanced_pollsdesccolumn[pn_multipleselect],
		$advanced_pollsdesccolumn[pn_multipleselectcount],
		$advanced_pollsdesccolumn[pn_recurring],
		$advanced_pollsdesccolumn[pn_recurringoffset],
		$advanced_pollsdesccolumn[pn_recurringinterval],
		$advanced_pollsdesccolumn[pn_language])
		VALUES (
		$nextId,
		'" . pnVarPrepForStore($pollname) . "',
		'" . pnVarPrepForStore($polldescription) . "',
		'" . (int)pnVarPrepForStore($polloptioncount) . "',
		'" . (int)pnVarPrepForStore($pollopendate) . "',
		'" . (int)pnVarPrepForStore($pollclosedate) . "',
		'" . (int)pnVarPrepForStore($pollvoteauthtype) . "',
		'" . (int)pnVarPrepForStore($polltiebreak) . "',
		'" . (int)pnVarPrepForStore($pollmultipleselect) . "',
		'" . (int)pnVarPrepForStore($pollmultipleselectcount) . "',
		'" . (int)pnVarPrepForStore($pollrecurring) . "',
		'" . (int)pnVarPrepForStore($pollrecurringoffset) . "',
		'" . (int)pnVarPrepForStore($pollrecurringinterval) . "',
		'" . pnVarPrepForStore($polllanguage) . "')";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSCREATEFAILED);
		return false;
	}
	 
	// Get the ID of the item that we inserted.  It is possible, although
	// very unlikely, that this is different from $nextId as obtained
	// above, but it is better to be safe than sorry in this situation
	$pollid = $dbconn->PO_Insert_ID($advanced_pollsdesctable, $advanced_pollsdesccolumn['pn_pollid']);
	 
	// Let any hooks know that we have created a new item.  As this is a
	// create hook we're passing 'pollid' as the extra info, which is the
	// argument that all of the other functions use to reference this
	// item
	pnModCallHooks('item', 'create', $pollid, 'pollid');
	 
	// Return the id of the newly created item to the calling process
	return $pollid;
}
 
/**
* delete a Poll item
* @param $args['pollid'] ID of the item
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_adminapi_delete($args) 
{
	// Get arguments from argument array - all arguments to this function
	// should be obtained from the $args array, getting them from other
	// places such as the environment is not allowed, as that makes
	// assumptions that will not hold in future versions of PostNuke
	extract($args);
	 
	// Argument check - make sure that all required arguments are present,
	// if not then set an appropriate error message and return
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}
	 
	// The user API function is called.  This takes the item ID which
	// we obtained from the input and gets us the information on the
	// appropriate item.  If the item does not exist we post an appropriate
	// message and return
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));
	 
	if ($item == false) {
		pnSessionSetVar('errormsg', _TEMPLATENOSUCHITEM);
		return true;
	}
	 
	// Security check - important to do this as early on as possible to
	// avoid potential security holes or just too much wasted processing.
	// However, in this case we had to wait until we could obtain the item
	// name to complete the instance information so this is the first
	// chance we get to do the check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_DELETE)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}
	 
	// Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
	// return arrays but we handle them differently.  For pnDBGetConn()
	// we currently just want the first item, which is the official
	// database handle.  For pnDBGetTables() we want to keep the entire
	// tables array together for easy reference later on
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	 
	// It's good practice to name the table and column definitions you
	// are getting - $table and $column don't cut it in more complex
	// modules
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];
	 
	// Delete the item - the formatting here is not mandatory, but it does
	// make the SQL statement relatively easy to read.  Also, separating
	// out the sql statement from the Execute() command allows for simpler
	// debug operation if it is ever needed
	$sql = "DELETE FROM $advanced_pollsdesctable
		WHERE $advanced_pollsdesccolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSDELETEFAILED);
		return false;
	}
	 
	// Delete the item - the formatting here is not mandatory, but it does
	// make the SQL statement relatively easy to read.  Also, separating
	// out the sql statement from the Execute() command allows for simpler
	// debug operation if it is ever needed
	$sql = "DELETE FROM $advanced_pollsdatatable
		WHERE $advanced_pollsdatacolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSDELETEFAILED);
		return false;
	}
	 
	// Let any hooks know that we have deleted an item.  As this is a
	// delete hook we're not passing any extra info
	pnModCallHooks('item', 'delete', $pollid, '');
	 
	// Let the calling process know that we have finished successfully
	return true;
}
 
/**
* update a poll
* @param $args['pollid'] the ID of the item
* @param $args['pollname'] the name of the poll to be updated
* @param $args['polldescription'] the name of the poll to be updated
* @param $args['polllanguage'] the number of the item to be updated
* @param $args['pollopendid'] the day component of the poll open date
* @param $args['pollopenmid'] the month component of the poll open date
* @param $args['pollopenyid'] the year component of the poll open date
* @param $args['pollopenhid'] the hour component of the poll open time
* @param $args['pollopenminid'] the minute component of the poll open time
* @param $args['pollclosedid'] the day component of the poll close date
* @param $args['pollclosemid'] the month component of the poll close date
* @param $args['pollcloseyid'] the year component of the poll close date
* @param $args['pollclosehid'] the hour component of the poll close time
* @param $args['pollcloseminid'] the minute component of the poll close time
* @param $args['polltiebreak'] the tiebreak methodlogy to use
* @param $args['pollvoteauthtype'] vote authorisation type to use
* @param $args['multipleselect'] type of poll selection
* @param $args['multipleselectcount'] number of selections allowed
* @param $args['pollrecurring'] is poll a recurring one
* @param $args['pollreucrringoffset'] offset for recurring polls
* @param $args['pollrecurringinterval'] interval to add for recurring polls
* @param $args['polloptioncount'] number of options for this poll
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_adminapi_update($args) 
{
	// Get arguments from argument array - all arguments to this function
	// should be obtained from the $args array, getting them from other
	// places such as the environment is not allowed, as that makes
	// assumptions that will not hold in future versions of PostNuke
	extract($args);

	//Not sure why unchecked checkboxes don't have a value
	if (!isset($pollmultipleselect)) {
		$pollmultipleselect = 0;
	}

	// Argument check - make sure that all required arguments are present,
	// if not then set an appropriate error message and return
	if ((!isset($pollid)) ||
		(!isset($pollname)) ||
		(!isset($polldescription)) ||
		(!isset($polllanguage)) ||
		(!isset($pollopendid)) ||
		(!isset($pollopenmid)) || 
		(!isset($pollopenyid)) ||
		(!isset($pollopenhid)) || 
		(!isset($pollopenminid)) ||
		(!isset($pollclosedid)) || 
		(!isset($pollclosemid)) ||
		(!isset($pollcloseyid)) ||
		(!isset($pollclosehid)) ||
		(!isset($pollcloseminid)) ||
		(!isset($pollvoteauthtype)) ||
		(!isset($polltiebreak)) ||
		(!isset($pollmultipleselect)) ||
		(!isset($pollmultipleselectcount)) ||
		(!isset($pollrecurring)) || 
		(!isset($pollrecurringoffset)) || 
		(!isset($pollrecurringinterval)) ||
		(!isset($polloptioncount)) ||
		(!isset($polloptions))) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSVARIABLEERROR);
		return false;
	}

	// The user API function is called.  This takes the item ID which
	// we obtained from the input and gets us the information on the
	// appropriate item.  If the item does not exist we post an appropriate
	// message and return
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check - important to do this as early on as possible to
	// avoid potential security holes or just too much wasted processing.
	// However, in this case we had to wait until we could obtain the item
	// name to complete the instance information so this is the first
	// chance we get to do the check

	// Note that at this stage we have two sets of item information, the
	// pre-modification and the post-modification.  We need to check against
	// both of these to ensure that whoever is doing the modification has
	// suitable permissions to edit the item otherwise people can potentially
	// edit areas to which they do not have suitable access
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_EDIT)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$pollname::$pollid", ACCESS_EDIT)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}

	// Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
	// return arrays but we handle them differently.  For pnDBGetConn()
	// we currently just want the first item, which is the official
	// database handle.  For pnDBGetTables() we want to keep the entire
	// tables array together for easy reference later on
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	// It's good practice to name the table and column definitions you
	// are getting - $table and $column don't cut it in more complex
	// modules
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];

	// Update the item - the formatting here is not mandatory, but it does
	// make the SQL statement relatively easy to read.  Also, separating
	// out the sql statement from the Execute() command allows for simpler
	// debug operation if it is ever needed
	$pollopendate = mktime ($pollopenhid, $pollopenminid, 0, $pollopenmid, $pollopendid, $pollopenyid);
	if (!$pollnoclosedate) {
		$pollclosedate = mktime ($pollclosehid, $pollcloseminid, 0, $pollclosemid, $pollclosedid, $pollcloseyid);
	} else {
	 	$pollclosedate = 0;
	}

	$sql = "UPDATE $advanced_pollsdesctable
		SET $advanced_pollsdesccolumn[pn_title] = '" . pnVarPrepForStore($pollname) . "',
		$advanced_pollsdesccolumn[pn_description] = '" . pnVarPrepForStore($polldescription) . "',
		$advanced_pollsdesccolumn[pn_optioncount] = '" . (int)pnVarPrepForStore($polloptioncount) . "',
		$advanced_pollsdesccolumn[pn_opendate] = '" . (int)pnVarPrepForStore($pollopendate) . "',
		$advanced_pollsdesccolumn[pn_closedate] = '" . (int)pnVarPrepForStore($pollclosedate) . "',
		$advanced_pollsdesccolumn[pn_voteauthtype] = '" . (int)pnVarPrepForStore($pollvoteauthtype) . "',
		$advanced_pollsdesccolumn[pn_tiebreakalg] = '" . (int)pnVarPrepForStore($polltiebreak) . "',
		$advanced_pollsdesccolumn[pn_multipleselect] = '" . (int)pnVarPrepForStore($pollmultipleselect) . "',
		$advanced_pollsdesccolumn[pn_multipleselectcount] = '" . (int)pnVarPrepForStore($pollmultipleselectcount) . "',
		$advanced_pollsdesccolumn[pn_closedate] = '" . (int)pnVarPrepForStore($pollclosedate) . "',
		$advanced_pollsdesccolumn[pn_recurring] = '" . (int)pnVarPrepForStore($pollrecurring) . "',
		$advanced_pollsdesccolumn[pn_recurringoffset] = '" . (int)pnVarPrepForStore($pollrecurringoffset) . "',
		$advanced_pollsdesccolumn[pn_recurringinterval] = '" . (int)pnVarPrepForStore($pollrecurringinterval) . "',
		$advanced_pollsdesccolumn[pn_language] = '" . pnVarPrepForStore($polllanguage) . "'
		WHERE $advanced_pollsdesccolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'";
	$dbconn->Execute($sql);

	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSUPDATEFAILED);
		return false;
	}

	// loop to populate 12 options into advanced_pollsdata table
	// note here to include future code to count number of options
	for ($optioncount = 0; $optioncount < count($polloptions); $optioncount++) {
		// Let's delete the option first and then re-insert
		$sql = "DELETE FROM $advanced_pollsdatatable
			    WHERE $advanced_pollsdatacolumn[pn_optionid] = '" . (int)pnVarPrepForStore($optioncount+1) . "' AND
			    $advanced_pollsdatacolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'";
		$dbconn->Execute($sql);
		// now do the reinsert
		$sql = "INSERT INTO $advanced_pollsdatatable (
			$advanced_pollsdatacolumn[pn_pollid],
			$advanced_pollsdatacolumn[pn_optionid],
			$advanced_pollsdatacolumn[pn_optiontext], 
			$advanced_pollsdatacolumn[pn_optioncolour])  
			VALUES (
			'" . (int)pnVarPrepForStore($pollid) . "',
			'" . (int)pnVarPrepForStore($optioncount+1) . "',
			'" . pnVarPrepForStore($polloptions[$optioncount]['optiontext']) . "',
			'" . pnVarPrepForStore($polloptions[$optioncount]['optioncolor']) . "');";
		$dbconn->Execute($sql);

		// Check for an error with the database code, and if so set an
		// appropriate error message and return
		if ($dbconn->ErrorNo()  != 0) {
			pnSessionSetVar('errormsg', _ADVANCEDPOLLSUPDATEFAILED);
			return false;
		}
		 
	}

	// Let the calling process know that we have finished successfully
	return true;
}

/**
* Reset vote counts to zero
* @param $args['pollid'] poll id for vote reset
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.1
* @version 1.1
*/
function advanced_polls_adminapi_resetvotes($args) 
{
	// Get arguments from argument array - all arguments to this function
	// should be obtained from the $args array, getting them from other places
	// such as the environment is not allowed, as that makes assumptions that
	// will not hold in future versions of PostNuke
	extract($args);

	// Argument check - make sure that all required arguments are present, if
	// not then set an appropriate error message and return
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}
	
	// The user API function is called.  This takes the item ID which we
	// obtained from the input and gets us the information on the appropriate
	// item.  If the item does not exist we post an appropriate message and
	// return
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_EDIT)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	} else {
		// get database connection
		$dbconn =& pnDBGetConn(true);
		$pntable =& pnDBGetTables();

		// define database tables
		$advanced_pollsvotestable = $pntable['advancedpollsvotes'];
		$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes'];
		$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
		$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];

		// empty votes table for this poll
		$sql = "DELETE FROM $advanced_pollsvotestable WHERE
			$advanced_pollsvotescolumn[pn_pollid]= '" . (int)pnVarPrepForStore($pollid) . "'";
		$result =& $dbconn->Execute($sql);

		// check for db errors
		if ($dbconn->ErrorNo()  != 0) {
			pnSessionSetVar('errormsg', _ADVANCEDPOLLSVOTESRESETFAILED);
			return false;
		}

		// close result set
		$result->Close();
	}
return true;
}

/**
* Get full admin info on all votes
* @param $args['pollid'] poll id for vote reset
* @param $args['sortorder'] ascending or desecending sort order
* @param $args['sortby'] sort field
* @returns array
* @return array of items, or false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.1
* @version 1.1
*/
function advanced_polls_adminapi_getvotes($args) 
{
	// Get arguments from argument array - all arguments to this function
	// should be obtained from the $args array, getting them from other places
	// such as the environment is not allowed, as that makes assumptions that
	// will not hold in future versions of PostNuke
	extract($args);

	// Argument check - make sure that all required arguments are present, if
	// not then set an appropriate error message and return
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// Optional arguments.
	if (!isset($startnum)) {
		$startnum = 1;
	}
	if (!isset($numitems)) {
		$numitems = -1;
	}
	if ((!isset($startnum))  || (!isset($numitems))) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	if (!isset($sortorder)) {
		$sortorder = 0;
	}
	if (!isset($sortby)) {
		$sortby = 1;
	}

	// The user API function is called.  This takes the item ID which we
	// obtained from the input and gets us the information on the appropriate
	// item.  If the item does not exist we post an appropriate message and
	// return
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_EDIT)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	} else {
		// get database connection
		$dbconn =& pnDBGetConn(true);
		$pntable =& pnDBGetTables();

		// define database tables
		$advanced_pollsvotestable = $pntable['advancedpollsvotes'];
		$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes'];

		switch ($sortby) {
			case 1:
				$sortstring = " ORDER BY $advanced_pollsvotescolumn[pn_voteid]";
				break;
			case 2:
				$sortstring = " ORDER BY $advanced_pollsvotescolumn[pn_ip]";
				break;
			case 3:
				$sortstring = " ORDER BY $advanced_pollsvotescolumn[pn_time]";
				break;
			case 4:
				$sortstring = " ORDER BY $advanced_pollsvotescolumn[pn_uid]";
				break;
			case 5:
				$sortstring = " ORDER BY $advanced_pollsvotescolumn[pn_voterank]";
				break;
			case 6:
				$sortstring = " ORDER BY $advanced_pollsvotescolumn[pn_optionid]";
				break;
			default:
				$sortstring = "";
		}
		
		if ($sortorder == 1 ) {
			$sortstring = $sortstring . " DESC";
		}
			
		// empty votes table for this poll
		$sql = "SELECT * FROM $advanced_pollsvotestable WHERE
			    $advanced_pollsvotescolumn[pn_pollid]= '" . (int)pnVarPrepForStore($pollid) . "'" . $sortstring;
        $result =& $dbconn->SelectLimit($sql, $numitems, $startnum-1);	
		//	$result =& $dbconn->Execute($sql);

		// check for db errors
		if ($dbconn->ErrorNo()  != 0) {
			pnSessionSetVar('errormsg', _ADVANCEDPOLLSVOTESGETFAILED);
			return false;
		}

		$votes = array();
		// Put items into result array.
		for (; !$result->EOF; $result->MoveNext()) {
			list($voteid, $voteip, $votetime, $voteuid, $voterank, $votepollid, $voteoptionid) = $result->fields;
			$votes[] = array('voteid' => $voteid,
							 'voteip' => $voteip,
							 'votetime' => $votetime,
							 'voteuid' => $voteuid,
							 'voterank' => $voterank,
							 'voteoptionid' => $voteoptionid);
		}

		// close result set
        // $result->Close();
	}
    return $votes;
}

/**
* Duplicate a poll
* @param $args['pollid'] poll id to duplicate
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.1
* @version 1.1
*/
function advanced_polls_adminapi_duplicate($args) 
{
	// Get arguments from argument array - all arguments to this function
	// should be obtained from the $args array, getting them from other places
	// such as the environment is not allowed, as that makes assumptions that
	// will not hold in future versions of PostNuke
	extract($args);

	// Argument check - make sure that all required arguments are present, if
	// not then set an appropriate error message and return
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// The user API function is called.  This takes the item ID which we
	// obtained from the input and gets us the information on the appropriate
	// item.  If the item does not exist we post an appropriate message and
	// return
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_ADD)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	} else {

		// The API function is called.  Note that the name of the API function and
		// the name of this function are identical, this helps a lot when
		// programming more complex modules.  The arguments to the function are
		// passed in as their own arguments array
		$pid = pnModAPIFunc('advanced_polls',
			'admin',
			'create',
			array('pollname' => $item['pn_title'],
			'polldescription' => $item['pn_description'],
			'polllanguage' => $item['pn_language'],
			'pollopendid' => date("d", $item['pn_opendate']),
			'pollopenmid' => date("n", $item['pn_opendate']),
			'pollopenyid' => date("Y", $item['pn_opendate']),
			'pollopenhid' => date("H", $item['pn_opendate']),
			'pollopenminid' => date("i", $item['pn_opendate']),
			'pollclosedid' => date("d", $item['pn_closedate']),
			'pollclosemid' => date("n", $item['pn_closedate']),
			'pollcloseyid' => date("Y", $item['pn_closedate']),
			'pollclosehid' => date("H", $item['pn_closedate']),
			'pollcloseminid' => date("i", $item['pn_closedate']),
			'polltiebreak' => $item['pn_tiebreakalg'],
			'pollvoteauthtype' => $item['pn_voteauthtype'],
			'pollmultipleselect' => $item['pn_multipleselect'],
			'pollmultipleselectcount' => $item['pn_multipleselectcount'],
			'pollrecurring' => $item['pn_recurring'],
			'pollrecurringoffset' => $item['pn_recurringoffset'],
			'pollrecurringinterval' => $item['pn_recurringinterval'],
			'polloptioncount' => $item['pn_optioncount']));

		if ($pid != false) {
			// Once the poll is created we call the modify function to add 
			// the poll options
			$result = pnModAPIFunc('advanced_polls',
				'admin',
				'update',
				array('pollid' => $pid,
				'pollname' => $item['pn_title'],
    			'polldescription' => $item['pn_description'],
				'polloptioncount' => $item['pn_optioncount'],
				'polllanguage' => $item['pn_language'],
				'pollopendid' => date("d", $item['pn_opendate']),
				'pollopenmid' => date("n", $item['pn_opendate']),
				'pollopenyid' => date("Y", $item['pn_opendate']),
				'pollopenhid' => date("H", $item['pn_opendate']),
				'pollopenminid' => date("i", $item['pn_opendate']),
				'pollclosedid' => date("d", $item['pn_closedate']),
				'pollclosemid' => date("n", $item['pn_closedate']),
				'pollcloseyid' => date("Y", $item['pn_closedate']),
				'pollclosehid' => date("H", $item['pn_closedate']),
				'pollcloseminid' => date("i", $item['pn_closedate']),
				'polltiebreak' => $item['pn_tiebreakalg'],
				'pollvoteauthtype' => $item['pn_voteauthtype'],
				'pollmultipleselect' => $item['pn_multipleselect'],
				'pollmultipleselectcount' => $item['pn_multipleselectcount'],
				'pollrecurring' => $item['pn_recurring'],
				'pollrecurringoffset' => $item['pn_recurringoffset'],
				'pollrecurringinterval' => $item['pn_recurringinterval'],
				'polloptions' => $item['pn_optionarray']));
		} else {
			$result = false;
		}

		// The return value of the function is checked here, and if the function
		// suceeded then an appropriate message is posted.  Note that if the
		// function did not succeed then the API function should have already
		// posted a failure message so no action is required
		if ($result != false) {
			// Success
			pnSessionSetVar('statusmsg', _ADVANCEDPOLLSCREATED);
		} else {
			// Failiure
			pnSessionSetVar('statusmsg', _ADVANCEDPOLLSFAILEDCREATE);
		}
	 
		return (bool)$result;
	}	
}

?>