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
* @return int poll item ID on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2008 by Mark West
* @since 1.0
*/
function advanced_polls_adminapi_create($args) 
{
    // Argument check
    if (!isset($args['title']) || !isset($args['description']) || !isset($args['optioncount'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$args[title]::", ACCESS_ADD)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

	$args['opendate'] = mktime($args['startHour'], $args['startMinute'], 0, $args['startMonth'], $args['startDay'], $args['startYear']);
	if (!$args['noclosedate']) {
		$args['closedate'] = mktime($args['closeHour'], $args['closeMinute'], 0, $args['closeMonth'], $args['closeDay'], $args['closeYear']);
	} else {
	 	$args['closedate'] = 0;
	}

    if (!DBUtil::insertObject($args, 'advanced_polls_desc', 'pollid')) {
        return LogUtil::registerError (_CREATEFAILED);
    }

    // Let any hooks know that we have created a new item.
    pnModCallHooks('item', 'create', $args['pollid'], array('module' => 'advanced_polls'));

    // An item was created, so we clear all cached pages of the items list.
    $pnRender = pnRender::getInstance('advanced_polls');
    $pnRender->clear_cache('advancedpolls_user_view.htm');

    // Return the id of the newly created item to the calling process
    return $args['pollid'];
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
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

    // Get the poll
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

    if ($item == false) {
        return LogUtil::registerError (_NOSUCHITEM);
    }
	 
	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_DELETE)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

    // Delete the object
    if (!DBUtil::deleteObjectByID('advanced_polls_votes', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (_DELETEFAILED);
    }
    if (!DBUtil::deleteObjectByID('advanced_polls_data', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (_DELETEFAILED);
    }
    if (!DBUtil::deleteObjectByID('advanced_polls_desc', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (_DELETEFAILED);
    }

	return true;
}
 
/**
* update a poll
* @param $args['pollid'] the ID of the item
* @param $args['title'] the name of the poll to be updated
* @param $args['description'] the name of the poll to be updated
* @param $args['language'] the number of the item to be updated
* @param $args['opendid'] the day component of the poll open date
* @param $args['openmid'] the month component of the poll open date
* @param $args['openyid'] the year component of the poll open date
* @param $args['openhid'] the hour component of the poll open time
* @param $args['openminid'] the minute component of the poll open time
* @param $args['closedid'] the day component of the poll close date
* @param $args['closemid'] the month component of the poll close date
* @param $args['closeyid'] the year component of the poll close date
* @param $args['closehid'] the hour component of the poll close time
* @param $args['closeminid'] the minute component of the poll close time
* @param $args['tiebreak'] the tiebreak methodlogy to use
* @param $args['voteauthtype'] vote authorisation type to use
* @param $args['multipleselect'] type of poll selection
* @param $args['multipleselectcount'] number of selections allowed
* @param $args['recurring'] is poll a recurring one
* @param $args['reucrringoffset'] offset for recurring polls
* @param $args['recurringinterval'] interval to add for recurring polls
* @param $args['optioncount'] number of options for this poll
* @return bool true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_adminapi_update($args) 
{
    // Argument check
    if (!isset($args['pollid']) || !isset($args['title']) || 
        !isset($args['description']) || !isset($args['optioncount'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

	// The user API function is called
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check

	// Note that at this stage we have two sets of item information, the
	// pre-modification and the post-modification.
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$args[title]::$pollid", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

	$args['opendate'] = mktime($args['startHour'], $args['startMinute'], 0, $args['startMonth'], $args['startDay'], $args['startYear']);
	if (!$args['noclosedate']) {
		$args['closedate'] = mktime($args['closeHour'], $args['closeMinute'], 0, $args['closeMonth'], $args['closeDay'], $args['closeYear']);
	} else {
	 	$args['closedate'] = 0;
	}

    // update the object
    if (!DBUtil::updateObject($args, 'advanced_polls_desc', '', 'pollid')) {
        return LogUtil::registerError (_UPDATEFAILED);
    }

    // first delete the poll options before reinserting them
    if (!DBUtil::deleteObjectByID('advanced_polls_data', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (_DELETEFAILED);
    }
    for ($count = 1; $count <= $args['optioncount']; $count++) {
        $items[] = array('pollid' => $args['pollid'],
                         'optiontext' => $args['options'][$count]['optiontext'],
                         'optioncolour' => $args['options'][$count]['optioncolor'],
                         'optionid' => $count);
    }
    if (!DBUtil::insertObjectArray($items, 'advanced_polls_data')) {
        return LogUtil::registerError (_UPDATEFAILED);
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
			return LogUtil::registerError(_GETFAILED);
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

