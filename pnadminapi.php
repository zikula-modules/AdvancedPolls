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
* Create a new Poll item
*
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
	// Get arguments from argument array
	extract($args);

	// Argument check
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
		return LogUtil::registerError(_ADVANCEDPOLLSVARIABLEERROR);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$pollname::", ACCESS_ADD)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}
	 
	// Get datbase setup
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];

	// Get next ID in table
	$nextId = $dbconn->GenId($advanced_pollsdesctable);

	$pollopendate = mktime ($pollopenhid, $pollopenminid, 0, $pollopenmid, $pollopendid, $pollopenyid);
	if (!$pollnoclosedate) {
		$pollclosedate = mktime ($pollclosehid, $pollcloseminid, 0, $pollclosemid, $pollclosedid, $pollcloseyid);
	} else {
	 	$pollclosedate = 0;
	}

	// Add item
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
	 
	// Check for an error with the database code
	if ($dbconn->ErrorNo()  != 0) {
		return LogUtil::registerError(_ADVANCEDPOLLSCREATEFAILED);
	}
	 
	// Get the ID of the item that we inserted.
	$pollid = $dbconn->PO_Insert_ID($advanced_pollsdesctable, $advanced_pollsdesccolumn['pn_pollid']);
	 
	// Let any hooks know that we have created a new item.
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
	// Get arguments from argument array
	extract($args);
	 
	// Argument check
	if (!isset($pollid)) {
		return LogUtil::registerError (_MODARGSERROR);
	}
	 
	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));
	 
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_DELETE)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

	// Get datbase setup
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];
	 
	// Delete the item
	$sql = "DELETE FROM $advanced_pollsdesctable
		WHERE $advanced_pollsdesccolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code
	if ($dbconn->ErrorNo()  != 0) {
		return LogUtil::registerError(_ADVANCEDPOLLSDELETEFAILED);
	}
	 
	// Delete the item
	$sql = "DELETE FROM $advanced_pollsdatatable
		WHERE $advanced_pollsdatacolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code
	if ($dbconn->ErrorNo()  != 0) {
		return LogUtil::registerError(_ADVANCEDPOLLSDELETEFAILED);
	}
	 
	// Let any hooks know that we have deleted an item.
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
	// Get arguments from argument array
	extract($args);

	// Not sure why unchecked checkboxes don't have a value
	if (!isset($pollmultipleselect)) {
		$pollmultipleselect = 0;
	}

	// Argument check
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
		return LogUtil::registerError(_ADVANCEDPOLLSVARIABLEERROR);
	}

	// The user API function is called
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check

	// Note that at this stage we have two sets of item information, the
	// pre-modification and the post-modification.
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$pollname::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

	// Get datbase setup
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];

	// Update the item
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
		return LogUtil::registerError(_ADVANCEDPOLLSUPDATEFAILED);
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
			return LogUtil::registerError(_ADVANCEDPOLLSUPDATEFAILED);
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
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid)) {
		return LogUtil::registerError (_MODARGSERROR);
	}
	
	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
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
			return LogUtil::registerError(_ADVANCEDPOLLSVOTESRESETFAILED);
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
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid)) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// Optional arguments.
	if (!isset($startnum)) {
		$startnum = 1;
	}
	if (!isset($numitems)) {
		$numitems = -1;
	}
	if ((!isset($startnum))  || (!isset($numitems))) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	if (!isset($sortorder)) {
		$sortorder = 0;
	}
	if (!isset($sortby)) {
		$sortby = 1;
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	} else {
		// get database setup
		$dbconn =& pnDBGetConn(true);
		$pntable =& pnDBGetTables();
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
			return LogUtil::registerError(_ADVANCEDPOLLSVOTESGETFAILED);
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
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid)) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls',
		'user',
		'get',
		array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_ADD)) {
		return LogUtil::registerError(_MODULENOAUTH);
	} else {

		// The API function is called.
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

		// The return value of the function is checked
		if ($result != false) {
			// Success
			LogUtil::registerStatus( _ADVANCEDPOLLSCREATED);
		} else {
			// Failiure
			LogUtil::registerStatus( _ADVANCEDPOLLSFAILEDCREATE);
		}
	 
		return (bool)$result;
	}	
}

