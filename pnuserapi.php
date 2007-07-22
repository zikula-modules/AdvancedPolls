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
* get all poll items
* @param $args['startnum'] starting poll id
* @param $args['numitems'] number of polls to get
* @param $args['checkml'] flag to check ml status
* @param $args['desc'] array title key name
* @return mixed array of items, or false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_getall($args)
{
	// Get arguments from argument array 
	extract($args);

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
	if (!isset($checkml)) {
		$checkml = true;
	}
	if (isset($desc) && $desc) {
		$desc = 'DESC';
	} else {
		$desc = '';
	}

	$items = array();

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::', '::', ACCESS_OVERVIEW)) {
		return $items;
	}

	// Get datbase setup
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];

	// Check if we is an ML situation
	$querylang = '';
	if ($checkml && pnConfigGetVar('multilingual') == 1) {
		$querylang = "WHERE ($advanced_pollsdesccolumn[pn_language]='" . pnVarPrepForStore(pnUserGetLang()) . "' 
					  OR $advanced_pollsdesccolumn[pn_language]='' 
					  OR $advanced_pollsdesccolumn[pn_language] IS NULL)";
	}

	// Get items
	$sql = "SELECT $advanced_pollsdesccolumn[pn_pollid],
				   $advanced_pollsdesccolumn[pn_title],
				   $advanced_pollsdesccolumn[pn_opendate],
				   $advanced_pollsdesccolumn[pn_closedate]
		FROM $advanced_pollsdesctable
		$querylang
		ORDER BY $advanced_pollsdesccolumn[pn_pollid] $desc";
	$result =& $dbconn->SelectLimit($sql, $numitems, $startnum-1);

	// Check for an error with the database
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _GETFAILED);
		return false;
	}

	// Put items into result array.
	for (; !$result->EOF; $result->MoveNext()) {
		list($pollid, $polltitle, $opendate, $closedate) = $result->fields;
		if (pnSecAuthAction(0, 'advanced_polls::item', "$polltitle::$pollid", ACCESS_READ)) {
			$items[] = array('pollid' => $pollid,
				             'polltitle' => $polltitle,
							 'opendate' => $opendate,
							 'closedate' => $closedate);
		}
	}

	// All successful database queries produce a result set, and that result
	// set should be closed when it has been finished with
	$result->Close();

	// Return the items
	return $items;
}

/**
* Get a specific Poll
* @param $args['pollid'] id of example item to get
* @param $args['idname'] array id key name
* @param $args['titlename'] array title key name
* @param $args['color'] array color key name
* @param $args['checkml'] flag to check ml status
* @return mixed item array, or false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_get($args) 
{
	static $polls = array();

	// Get arguments from argument array
	extract($args);
	 
	// Argument check
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}
	
	if (isset($polls[$pollid])) {
		return $polls[$pollid];
	}
	
	if (!isset($checkml)) {
		$checkml = true;
	}

	// Get datbase setup
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];

	// Check if we is an ML situation
	$querylang = '';
	if ($checkml && pnConfigGetVar('multilingual') == 1) {
		$querylang = "AND ($advanced_pollsdesccolumn[pn_language]='" . pnVarPrepForStore(pnUserGetLang()) . "' 
					  OR $advanced_pollsdesccolumn[pn_language]='' 
					  OR $advanced_pollsdesccolumn[pn_language] IS NULL)";
	}
	 
	// Get item
	$sql = "SELECT $advanced_pollsdesccolumn[pn_title],
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
		$advanced_pollsdesccolumn[pn_language]
		FROM $advanced_pollsdesctable
		WHERE $advanced_pollsdesccolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "' 
		$querylang";
	$result =& $dbconn->Execute($sql);

	// Check for an error with the database code, and if so set an appropriate
	// error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _GETFAILED);
		return false;
	}

	// Check for no rows found, and if so return
	if ($result->EOF) {
		return false;
	}

	// Obtain the item information from the result set
	list($polltitle, $polldescription, $polloptioncount, $pollopendate, $pollclosedate, $pollvoteauthtype, $polltiebreak, $pollmultipleselect,
		$pollmultipleselectcount, $pollrecurring, $pollrecurringoffset, $pollrecurringinterval, $polllanguage) = $result->fields;

	//close result set
	$result->Close();

	// It's good practice to name the table and column definitions you are
	// getting - $table and $column don't cut it in more complex modules
	$advanced_pollsdatatable = $pntable['advancedpollsdata'];
	$advanced_pollsdatacolumn = &$pntable['advanced_polls_data'];

	// Get item
	$sql = "SELECT $advanced_pollsdatacolumn[pn_optionid],
			$advanced_pollsdatacolumn[pn_optiontext],
			$advanced_pollsdatacolumn[pn_optioncolour]
			FROM $advanced_pollsdatatable
			WHERE $advanced_pollsdatacolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'
			ORDER BY $advanced_pollsdatacolumn[pn_optionid]";

	$result =& $dbconn->Execute($sql);

	// Obtain the item information from the result set
	$pn_optionarray = array();
	for (; !$result->EOF; $result->MoveNext()) {
		list($voteid, $optiontext, $optioncolour) = $result->fields;
		if ($optiontext) {
			$pn_optionarray[$voteid-1] = array('voteid' => $voteid,
				                               'optiontext' => $optiontext,
									           'optioncolour' => $optioncolour);
		}
	}

	// pad the array to the correct number of poll options
	if (count($pn_optionarray) < $polloptioncount) {
	    for($counter = 0; $counter < $polloptioncount; $counter++) {
		    if (!isset($pn_optionarray[$counter])) {
				$pn_optionarray[$counter] = array('voteid' => $counter,
										          'optiontext' => '',
										          'optioncolour' => '');
			}
		}
	}

	// All successful database queries produce a result set, and that result
	// set should be closed when it has been finished with
	$result->Close();

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$polltitle::$pollid", ACCESS_READ)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}

	// Create the item array
	$item = array('pn_pollid' => $pollid,
				'pn_title' => $polltitle,
				'pn_description' => $polldescription,
				'pn_optioncount' => $polloptioncount,
				'pn_opendate' => $pollopendate,
				'pn_closedate' => $pollclosedate,
				'pn_voteauthtype' => $pollvoteauthtype,
				'pn_tiebreakalg' => $polltiebreak,
				'pn_multipleselect' => $pollmultipleselect,
				'pn_multipleselectcount' => $pollmultipleselectcount,
				'pn_recurring' => $pollrecurring,
				'pn_recurringoffset' => $pollrecurringoffset,
				'pn_recurringinterval' => $pollrecurringinterval,
				'pn_language' => $polllanguage,
				'pn_optionarray' => $pn_optionarray );

	$polls[$pollid] = $item;

	// Return the item array
	return $item;
}

/**
* utility function to count the number of items held by this module
* @param $args['checkml'] flag to check ml status
* @return integer number of items held by this module
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_countitems($args) 
{
	// Get arguments from argument array
	extract($args);

	// defauls	
	if (!isset($checkml)) {
		$checkml = true;
	}

	// Get datbase setup
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsdesctable = $pntable['advancedpollsdesc'];
	$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc'];

	// Check if we is an ML situation
	$querylang = '';
	if ($checkml && pnConfigGetVar('multilingual') == 1) {
		$querylang = "WHERE ($advanced_pollsdesccolumn[pn_language]='" . pnVarPrepForStore(pnUserGetLang()) . "' 
					  OR $advanced_pollsdesccolumn[pn_language]='' 
					  OR $advanced_pollsdesccolumn[pn_language] IS NULL)";
	}

	// Get item count
	$sql = "SELECT COUNT(1) FROM $advanced_pollsdesctable $querylang";
	$result =& $dbconn->Execute($sql);

	// Check for an error with the database code, and if so set an appropriate
	// error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _GETFAILED);
		return false;
	}

	// Obtain the number of items
	list($numitems) = $result->fields;

	// All successful database queries produce a result set, and that result
	// set should be closed when it has been finished with
	$result->Close();

	// Return the number of items
	return $numitems;
}

/**
* check if poll is open
* @param $args['pollid'] id of example item to get
* @return bool false if closed, true if open
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_isopen($args) 
{
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// no such item is db
	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_OVERVIEW)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}

	//establish current date and time
	$currentdate = time();

	//establish poll open date and time
	$opendate = $item['pn_opendate'];

	//establish poll close date and time
	$closedate = $item['pn_closedate'];

	//is poll open?
	if (($currentdate >= $opendate) && (($currentdate <= $closedate) || $closedate == 0)) {
		return true;
	} else {
		return false;
	}
}

/**
* check if user has voted in poll
* @param $args['pollid'] id of poll item to get
* @return bool true on vote allowed, false on vote not allowed
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_isvoteallowed($args) 
{
	static $uservotinghistory;

	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// no such item in db
	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_READ)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_COMMENT)) {
		// Here we don't set an error as this indicates that the user can't vote in this poll
		return false;
	}

	// get db information for use later
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	$advanced_pollsvotestable = $pntable['advancedpollsvotes'];
	$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes'];

	// get voting authorisation from item array
	$voteauthtype = $item['pn_voteauthtype'];

	switch ($voteauthtype) {
		case 1: //Free voting

		// voting always allowed
		return true;

		case 2: //UID Voting

		// extract user id from session variables
		$uid = pnUserGetVar('uid');

		if (!is_array($uservotinghistory[$uid])) {
			// check user id against db
			$sql = "SELECT $advanced_pollsvotescolumn[pn_pollid]
					FROM $advanced_pollsvotestable
					WHERE
					$advanced_pollsvotescolumn[pn_uid]= '" . (int)pnVarPrepForStore($uid) . "'";
			$result =& $dbconn->Execute($sql);
	
			$items = array();
			for (; !$result->EOF; $result->MoveNext()) {
				list($respollid) = $result->fields;
				$items[$respollid] = true;
			}
	
			//close result set
			$result->Close();

			$uservotinghistory[$uid] = $items;
		}

		if (isset($uservotinghistory[$uid][$pollid])) {
			return false;
		} else {
			return true;
		}

		case 3: //Cookie voting

		// check for existance of session variable (cookie)
		// if set then vote is invalid otherwise set session variable
		// and return valid
		if (pnSessionGetVar("advanced_polls_voted$pollid")) {
			return false;
		} else {
			return true;
		}

		case 4: //IP address voting

		// extract ip from http headers
		$ip = pnServerGetVar("REMOTE_ADDR");

		// check ip against db
		$sql = "SELECT $advanced_pollsvotescolumn[pn_ip]
				FROM $advanced_pollsvotestable
				WHERE
				(($advanced_pollsvotescolumn[pn_pollid]='" . (int)pnVarPrepForStore($pollid) . "') AND
				($advanced_pollsvotescolumn[pn_ip]='" . pnVarPrepForStore($ip) . "'))";
		$result =& $dbconn->Execute($sql);

		//If there are no rows back from this query then this uid can vote
		if ($result->EOF) {
			return true;
		} else {
			return false;
		}

		//close result set
		$result->Close();

		return true;

		case 5: //Cookie + IP address voting

		// possibly remove this voting style
		return true;

		default: //any other option - should never occur
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTHTYPE);
		return false;
	}
}

/**
* reset polls votes if poll is recurring poll
* @param $args['pollid'] id of example item to get
* @return bool true on reset success, false on vote failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_resetrecurring($args) 
{
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// The user API function is called. 
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_READ)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}

	// convert recurring offset into unix timestamp format
	$offset = $item['pn_recurringoffset'] * 60 * 60;
	$closetimewithoffset = $item['pn_closedate'] + $offset;

	// if this poll is currently closed and poll it set to reoccur
	// then update relevant db tables
	// Doesn't call IsPollOpen API as this checks for both before
	// poll open date and after poll close date
	// We are only insterest in Poll cose date
	if (($closetimewithoffset < time()) and ($item['pn_recurring'] == 1)) {

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
				$advanced_pollsvotescolumn[pn_pollid]='" . (int)pnVarPrepForStore($pollid) . "'";
		$result =& $dbconn->Execute($sql);

		// check for db errors
		if ($dbconn->ErrorNo()  != 0) {
			pnSessionSetVar('errormsg', _ADVANCEDPOLLSRESETFAILED);
			return false;
		}

		// set new opening and closing times
		// calculate recurrance interval in seconds from db value in days
		$recurranceinterval = $item[pn_recurringinterval] * 24 * 60 * 60;

		//new open is close time with offset calculated earlier in this function
		$newopentime = $closetimewithoffset;
		$newclosetime = $item[pn_closedate] + $recurranceinterval;

		// close result set
		$result->Close();

		// update poll close and open times
		$sql = "UPDATE $advanced_pollsdesctable SET
				$advanced_pollsdesccolumn[pn_opendate] = '".(int)pnVarPrepForStore($newopentime)."',
				$advanced_pollsdesccolumn[pn_closedate] = '".(int)pnVarPrepForStore($newclosetime)."'
				WHERE
				$advanced_pollsdesccolumn[pn_pollid]= '" . (int)pnVarPrepForStore($pollid) . "'";
		$result =& $dbconn->Execute($sql);

		// check for db errors
		if ($dbconn->ErrorNo()  != 0) {
			pnSessionSetVar('errormsg', _ADVANCEDPOLLSRESETFAILED);
			return false;
		}

		// close result set
		$result->Close();

	}
	return true;
}

/**
* get counts of votes in a poll leading vote and total vote count
* @param $args['pollid'] id of example item to get
* @return mixed voting array on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_pollvotecount($args) 
{
	// Get arguments from argument array
	extract($args);
	 
	// Argument check
	if (!isset($pollid)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	if ($item == false) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOSUCHITEM);
		return false;
	}

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_OVERVIEW)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}

	// get database connection
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	// define database tables
	$advanced_pollsvotestable = $pntable['advancedpollsvotes'];
	$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes'];

	if ($item['pn_multipleselect'] == 0) {
		//GPK  We have a NON-multiselect poll, so
		// find the total number of votes in this poll
		$sql = "SELECT COUNT($advanced_pollsvotescolumn[pn_optionid])
			FROM $advanced_pollsvotestable WHERE
			$advanced_pollsvotescolumn[pn_pollid]='".(int)pnVarPrepForStore($pollid)."'";

		$result =& $dbconn->Execute($sql);
		list($totalvotecount) = $result->fields;
	} else {
		//GPK  We have a multi-select poll, so we want to find out the largest
		//GPK  number of votes cast for any item.  This will only work for
		//GPK  sites which do not allow anonymous votes. 
		$sql = "SELECT $advanced_pollsvotescolumn[pn_optionid], COUNT(*) AS total_votes
		FROM nuke_advanced_polls_votes WHERE
		$advanced_pollsvotescolumn[pn_pollid]='".(int)pnVarPrepForStore($pollid)."' GROUP BY pn_optionid ASC";
		$result =& $dbconn->Execute($sql);
		//GPK We just need the first entry since that one will give us largest number of votes.
		(list($optID, $totalvotecount) = $result->fields);
		
		//GPK For systems which allow anonymous we need to find out the number of anonymous votes
		$sql = "SELECT $advanced_pollsvotescolumn[pn_optionid], COUNT(*) AS total_votes
		FROM nuke_advanced_polls_votes WHERE
		$advanced_pollsvotescolumn[pn_pollid]='".(int)pnVarPrepForStore($pollid)."' AND
		pn_uid = 0 GROUP BY pn_optionid DESC";
		$result =& $dbconn->Execute($sql);

		$anonymousvotecount = 0;
		
		list($optID, $anonymousvotecount) = $result->fields;
		$totalvotecount = $totalvotecount + $anonymousvotecount;
	}

	$pn_votecountarray = array();
	$recordcount = 0;

	// Set initial vote id
	$leadingvotecount = 0;
	$leadingvoteid = 0;

    // for ease of backwards compatabilty lets check for a 0 option count
	if ($item['pn_optioncount'] == 0) {
		$item['pn_optioncount'] = pnModGetVar('advanced_polls', 'defaultoptioncount');
	}

	// now lets get all the vote counts
	$sql = "SELECT COUNT($advanced_pollsvotescolumn[pn_optionid]),
						$advanced_pollsvotescolumn[pn_optionid]
			FROM $advanced_pollsvotestable WHERE
			$advanced_pollsvotescolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "'
			GROUP BY $advanced_pollsvotescolumn[pn_optionid]";
	$result =& $dbconn->Execute($sql);

	// Check for no rows found, and if so return
	if ($result->EOF) {
		return false;
	}

    for (; !$result->EOF; $result->MoveNext()) {
		list($count, $optionid) = $result->fields; 
		$pn_votecountarray[$optionid] = $count;
	}

	for ($i = 1, $max = $item['pn_optioncount']; $i <= $max; $i++) {
		if (!isset($pn_votecountarray[$i])) {
			$pn_votecountarray[$i] = 0;
		}
		if (($pn_votecountarray[$i] == $leadingvotecount) and ($item['pn_tiebreakalg']) > 0) {
			if ($item['pn_tiebreakalg'] == 1) {
				$leadingvoteid = pnModAPIFunc('advanced_polls', 'user',	'timecountback',
 								array('pollid' => $pollid, 'voteid1' => $leadingvoteid,	'voteid2' => $i));
			}
		}

		if ($pn_votecountarray[$i] > $leadingvotecount) {
			$leadingvotecount = $pn_votecountarray[$i];
			$leadingvoteid = $i;
		}

	}

	// Create the item array
	$item = array('pn_totalvotecount' => $totalvotecount,
				  'pn_leadingvoteid' => $leadingvoteid,
				  'pn_votecountarray' => $pn_votecountarray );

	// Return the item array
	return $item;

}

/**
* Adds vote to db
* @param $args['pollid'] id of example item to get
* @param $args['voteid'] poll item to register vote for
* @param $args['voterank'] ranking of vote in multiple select polls
* @return bool true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_addvote($args) 
{
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid) || !isset($voteid) || !isset($voterank)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// Security check
	if (pnSecAuthAction(0,'advanced_polls::item',"$polltitle::$pollid",ACCESS_OVERVIEW)) {
		if (pnSecAuthAction(0,'advanced_polls::item',"$polltitle::$pollid",ACCESS_COMMENT)) {

			$dbconn =& pnDBGetConn(true);
			$pntable =& pnDBGetTables();
			$advanced_pollsvotestable = $pntable['advancedpollsvotes'];
			$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes'];

			// Get next ID in table 
			$nextId = $dbconn->GenId($advanced_pollsvotestable);

			// extract user id from session variables
			$uid = pnUserGetVar('uid');
			if (!isset($uid)) {
				$uid = 0;
			}

			// add first part of vote
			$sql = "INSERT INTO $advanced_pollsvotestable (
				$advanced_pollsvotescolumn[pn_voteid],
				$advanced_pollsvotescolumn[pn_ip],
				$advanced_pollsvotescolumn[pn_uid],
				$advanced_pollsvotescolumn[pn_time],
				$advanced_pollsvotescolumn[pn_pollid],
				$advanced_pollsvotescolumn[pn_optionid],
				$advanced_pollsvotescolumn[pn_voterank])
				VALUES (
				'" . (int)pnVarPrepForStore($nextId) . "',
				'" . pnVarPrepForStore(pnServerGetVar("REMOTE_ADDR")) . "',
				'" . (int)pnVarPrepForStore($uid) . "',
				'" . (int)pnVarPrepForStore(time()) . "',
				'" . (int)pnVarPrepForStore($pollid) ."',
				'" . (int)pnVarPrepForStore($voteid) ."',
				'" . (int)pnVarPrepForStore($voterank) ."')";

			$result =& $dbconn->Execute($sql);

			if ($dbconn->ErrorNo()  != 0) {
				pnSessionSetVar('errormsg', _ADVANCEDPOLLSVOTEFAILED);
				return false;
			}

			//set cookie to indicate vote made in this poll
			//used only with cookie based voting but set all the time
			//in case admin changes voting regs.
			pnSessionSetVar("advanced_polls_voted$pollid", 1);

			return true;
		}
	}
}

/**
* Performs time count back on votes
*
* This function sums all the unix timestamps for two poll item ids 
* and returns the item id with the lowest sum. This is used as a tiebreak
* methodology
*
* @param $args['pollid'] id of example item to get
* @param $args['voteid1'] first poll item id
* @param $args['voteid2'] second poll item id
* @return mixed integer $voteid or false on vote failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_timecountback($args) 
{
	// Get arguments from argument array
	extract($args);

	// Argument check
	if (!isset($pollid) || !isset($voteid1) || !isset($voteid2)) {
		pnSessionSetVar('errormsg', _MODARGSERROR);
		return false;
	}

	// get database connection
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	$advanced_pollsvotestable = $pntable['advancedpollsvotes'];
	$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes'];

	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// Security check
	if (!pnSecAuthAction(0, 'advanced_polls::item', "$item[pn_title]::$pollid", ACCESS_OVERVIEW)) {
		pnSessionSetVar('errormsg', _ADVANCEDPOLLSNOAUTH);
		return false;
	}

	$sql = "SELECT SUM($advanced_pollsvotescolumn[pn_time])
			FROM $advanced_pollsvotestable WHERE
			(($advanced_pollsvotescolumn[pn_pollid] = '". (int)pnVarPrepForStore($pollid) . "') AND
			($advanced_pollsvotescolumn[pn_optionid] = '" . (int)pnVarPrepForStore($voteid1) . "'))";
	$result =& $dbconn->Execute($sql);
	list($firstsum) = $result->fields;

	$sql = "SELECT SUM($advanced_pollsvotescolumn[pn_time])
			FROM $advanced_pollsvotestable WHERE
			(($advanced_pollsvotescolumn[pn_pollid] = '" . (int)pnVarPrepForStore($pollid) . "') AND
			($advanced_pollsvotescolumn[pn_optionid] = '" . (int)pnVarPrepForStore($voteid2) . "'))";
	$result =& $dbconn->Execute($sql);
	list($secondsum) = $result->fields;

	if ($firstsum < $secondsum) {
		return $voteid1;
	} else {
		return $voteid2;
	}
}

/**
* Gets id of last poll to close
* @return int id of last poll to close, 0 if no closed polls
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.05
* @version 1.1
*/
function advanced_polls_userapi_getlastclosed($args) 
{
	// Get arguments from argument arra
	extract($args);

	// The API function is called.
	$items = pnModAPIFunc('advanced_polls', 'user', 'getall');

    // work out which poll has closed most recently
	$lastclosed = 0;
	foreach ($items as $item) {
		if ($item['opendate'] < time() && $item['closedate'] < time() && $item['closedate'] != 0) {
			$lastclosed = $item['pollid'];
		}
	}

	return $lastclosed;

}

/**
* Gets a random poll id
* @return int id of poll
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_userapi_getrandom() 
{
	// seed with microseconds
	function make_seed() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}
	srand(make_seed());
	
	// The API function is called.
	$items = pnModAPIFunc('advanced_polls',	'user', 'getall');

	$randomitemid = array_rand($items , 1);
	$randomitem = $items[$randomitemid];
	$pollid = $randomitem['pollid'];

	return $pollid;
}

