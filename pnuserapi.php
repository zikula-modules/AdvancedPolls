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
    // Optional arguments.
    if (!isset($args['startnum']) || empty($args['startnum'])) {
        $args['startnum'] = 0;
    }
    if (!isset($args['numitems']) || empty($args['numitems'])) {
        $args['numitems'] = -1;
    }
	if (!isset($args['checkml'])) {
		$args['checkml'] = true;
	}
	if (isset($args['desc']) && $args['desc']) {
		$args['desc'] = 'DESC';
	} else {
		$args['desc'] = '';
	}

    if (!is_numeric($args['startnum']) ||
        !is_numeric($args['numitems'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

	$items = array();

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
		return $items;
	}

    $args['catFilter'] = array();
    if (isset($args['category']) && !empty($args['category'])){
        if (is_array($args['category'])) { 
            $args['catFilter'] = $args['category'];
        } elseif (isset($args['property'])) {
            $property = $args['property'];
            $args['catFilter'][$property] = $args['category'];
        }
    }

    // populate an array with each part of the where clause and then implode the array if there is a need.
    // credit to Jorg Napp for this technique - markwest
    $pntable = pnDBGetTables();
    $pollscolumn = $pntable['advanced_polls_desc_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && $args['checkml']) {
        $queryargs[] = "($pollscolumn[language]='" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $pollscolumn[language]='')";
    }

    $where = null;
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }

    // define the permission filter to apply
    $permFilter = array(array('realm'           => 0,
                              'component_left'  => 'advanced_polls',
                              'component_right' => 'item',
                              'instance_left'   => 'polltitle',
                              'instance_right'  => 'pollid',
                              'level'           => ACCESS_READ));

    // get the objects from the db
    $items = DBUtil::selectObjectArray('advanced_polls_desc', $where, 'pollid', $args['startnum']-1, $args['numitems'], '', $permFilter, $args['catFilter']);

    // Check for an error with the database code, and if so set an appropriate
    // error message and return
    if ($items === false) {
        return LogUtil::registerError (_GETFAILED);
    }

    // need to do this here as the category expansion code can't know the
    // root category which we need to build the relative path component
     if ($items && isset($args['catregistry']) && $args['catregistry']) {
        if (!($class = Loader::loadClass ('CategoryUtil'))) {
            pn_exit (pnML('_UNABLETOLOADCLASS', array('s' => 'CategoryUtil')));
	    }
        ObjectUtil::postProcessExpandedObjectArrayCategories ($items, $args['catregistry']);
    }

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
    // optional arguments
    if (isset($args['objectid'])) {
       $args['pollid'] = $args['objectid'];
    }

    // Argument check
    if (!isset($args['pollid']) || !is_numeric($args['pollid'])) {
        return LogUtil::registerError (_MODARGSERROR);
    }

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
		return false;
	}

    // populate an array with each part of the where clause and then implode the array if there is a need.
    // credit to Jorg Napp for this technique - markwest
    $pntable = pnDBGetTables();
    $pollscolumn = $pntable['advanced_polls_desc_column'];
    $queryargs = array();
    if (pnConfigGetVar('multilingual') == 1 && $args['checkml']) {
        $queryargs[] = "($pollscolumn[language]='" . DataUtil::formatForStore(pnUserGetLang()) . "' OR $pollscolumn[language]='')";
    }

    $where = null;
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }

    // define the permission filter to apply
    $permFilter = array(array('realm'           => 0,
                              'component_left'  => 'advanced_polls',
                              'component_right' => 'item',
                              'instance_left'   => 'polltitle',
                              'instance_right'  => 'pollid',
                              'level'           => ACCESS_READ));

    $poll = DBUtil::selectObjectByID('advanced_polls_desc', $args['pollid'], 'pollid', '', $permFilter);
    $poll['options'] = DBUtil::selectObjectArray('advanced_polls_data', 'pn_pollid=\''.DataUtil::formatForStore($poll['pollid']).'\'', 'optionid');

	// pad the array to the correct number of poll options
	if (count($poll['options']) < $poll['optioncount']) {
	    for($counter = 0; $counter < $poll['optioncount']; $counter++) {
		    if (!isset($poll['options'][$counter])) {
				$poll['options'][$counter] = array('voteid' => $counter,
										           'optiontext' => '',
										           'optioncolour' => '');
			}
		}
	}

	// Return the item array
	return $poll;
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
    $args['catFilter'] = array();
    if (isset($args['category']) && !empty($args['category'])){
        if (is_array($args['category'])) { 
            $args['catFilter'] = $args['category'];
        } elseif (isset($args['property'])) {
            $property = $args['property'];
            $args['catFilter'][$property] = $args['category'];
        }
    }

	// defauls	
	if (!isset($checkml)) {
		$checkml = true;
	}

	// Check if we is an ML situation
	$querylang = '';
	if ($checkml && pnConfigGetVar('multilingual') == 1) {
		$querylang = "WHERE ($advanced_pollsdesccolumn[language]='" . pnVarPrepForStore(pnUserGetLang()) . "' 
					  OR $advanced_pollsdesccolumn[language]='' 
					  OR $advanced_pollsdesccolumn[language] IS NULL)";
	}

    // Return the number of items
    return DBUtil::selectObjectCount('advanced_polls_desc', $querylang, 'pollid', false, $args['catFilter']);
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
	// Argument check
	if (!isset($args['pollid'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

	// no such item is db
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[vpollid]", ACCESS_OVERVIEW)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

	//establish current date and time
	$currentdate = time();

	//establish poll open date and time
	$opendate = $item['opendate'];

	//establish poll close date and time
	$closedate = $item['closedate'];

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
	// Argument check
	if (!isset($args['pollid'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

	// no such item in db
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_READ)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_COMMENT)) {
		// Here we don't set an error as this indicates that the user can't vote in this poll
		return false;
	}

	// get voting authorisation from item array
	$voteauthtype = $item['voteauthtype'];

	switch ($voteauthtype) {
		case 1: //Free voting
			// voting always allowed
			return true;
		case 2: //UID Voting
			// extract user id from session variables
			$uid = pnVarPrepForStore(pnUserGetVar('uid'));

            // get all the matching votes
            $where = "WHERE pn_uid = '{$uid}' AND pn_pollid = '{$args['pollid']}'";
            $votes = DBUtil::selectObjectArray('advanced_polls_votes', $where, 'pollid', -1, -1, 'pollid');

			if (isset($votes[$args['pollid']])) {
				return false;
			} else {
				return true;
			}
		case 3: //Cookie voting
			// check for existance of session variable (cookie)
			// if set then vote is invalid otherwise set session variable
			// and return valid
			if (pnSessionGetVar("advanced_polls_voted{$args['pollid']}")) {
				return false;
			} else {
				return true;
			}
		case 4: //IP address voting
			// extract ip from http headers
			$ip = ServerUtil::getVar('REMOTE_ADDR');

            // get all the matching votes
            $where = "pn_ip = '{$ip}' AND pn_pollid = '{$args['pollid']}'";
            $votes = DBUtil::selectObjectArray('advanced_polls_votes', $where, 'pollid', -1, -1, 'pollid');

			//If there are no rows back from this query then this uid can vote
			if ($votes) {
				return true;
			} else {
				return false;
			}

			return true;

		case 5: //Cookie + IP address voting
			// possibly remove this voting style
			return true;

		default: //any other option - should never occur
			return LogUtil::registerError(_ADVANCEDPOLLSNOAUTHTYPE);
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
		return LogUtil::registerError (_MODARGSERROR);
	}

	// The user API function is called. 
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_READ)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

	// convert recurring offset into unix timestamp format
	$offset = $item['recurringoffset'] * 60 * 60;
	$closetimewithoffset = $item['closedate'] + $offset;

	// if this poll is currently closed and poll it set to reoccur
	// then update relevant db tables
	// Doesn't call IsPollOpen API as this checks for both before
	// poll open date and after poll close date
	// We are only insterest in Poll cose date
	if (($closetimewithoffset < time()) and ($item['recurring'] == 1)) {

		// get database connection
		$dbconn =& pnDBGetConn(true);
		$pntable =& pnDBGetTables();

		// define database tables
		$advanced_pollsvotestable = $pntable['advanced_polls_votes'];
		$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes_column'];
		$advanced_pollsdesctable = $pntable['advanced_polls_desc'];
		$advanced_pollsdesccolumn = &$pntable['advanced_polls_desc_column'];

		// empty votes table for this poll
		$sql = "DELETE FROM $advanced_pollsvotestable WHERE
				$advanced_pollsvotescolumn[pollid]='" . (int)pnVarPrepForStore($pollid) . "'";
		$result =& $dbconn->Execute($sql);

		// check for db errors
		if ($dbconn->ErrorNo()  != 0) {
			return LogUtil::registerError(_ADVANCEDPOLLSRESETFAILED);
		}

		// set new opening and closing times
		// calculate recurrance interval in seconds from db value in days
		$recurranceinterval = $item['recurringinterval'] * 24 * 60 * 60;

		//new open is close time with offset calculated earlier in this function
		$newopentime = $closetimewithoffset;
		$newclosetime = $item['closedate'] + $recurranceinterval;

		// close result set
		$result->Close();

		// update poll close and open times
		$sql = "UPDATE $advanced_pollsdesctable SET
				$advanced_pollsdesccolumn[opendate] = '".(int)pnVarPrepForStore($newopentime)."',
				$advanced_pollsdesccolumn[closedate] = '".(int)pnVarPrepForStore($newclosetime)."'
				WHERE
				$advanced_pollsdesccolumn[pollid]= '" . (int)pnVarPrepForStore($pollid) . "'";
		$result =& $dbconn->Execute($sql);

		// check for db errors
		if ($dbconn->ErrorNo()  != 0) {
			return LogUtil::registerError(_ADVANCEDPOLLSRESETFAILED);
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
	// Argument check
	if (!isset($args['pollid'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_OVERVIEW)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

	// get database connection
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	// define database tables
	$advanced_pollsvotestable = $pntable['advanced_polls_votes'];
	$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes_column'];

	if ($item['multipleselect'] == 0) {
		//GPK  We have a NON-multiselect poll, so
		// find the total number of votes in this poll
		$sql = "SELECT COUNT($advanced_pollsvotescolumn[optionid])
			FROM $advanced_pollsvotestable WHERE
			$advanced_pollsvotescolumn[pollid]='".(int)pnVarPrepForStore($args['pollid'])."'";

		$result =& $dbconn->Execute($sql);
		list($totalvotecount) = $result->fields;
	} else {
		//GPK  We have a multi-select poll, so we want to find out the largest
		//GPK  number of votes cast for any item.  This will only work for
		//GPK  sites which do not allow anonymous votes. 
		$sql = "SELECT $advanced_pollsvotescolumn[optionid], COUNT(*) AS total_votes
		FROM nuke_advanced_polls_votes WHERE
		$advanced_pollsvotescolumn[pollid]='".(int)pnVarPrepForStore($args['pollid'])."' GROUP BY pn_optionid ASC";
		$result =& $dbconn->Execute($sql);
		//GPK We just need the first entry since that one will give us largest number of votes.
		(list($optID, $totalvotecount) = $result->fields);
		
		//GPK For systems which allow anonymous we need to find out the number of anonymous votes
		$sql = "SELECT $advanced_pollsvotescolumn[optionid], COUNT(*) AS total_votes
		FROM nuke_advanced_polls_votes WHERE
		$advanced_pollsvotescolumn[pollid]='".(int)pnVarPrepForStore($args['pollid'])."' AND
		pn_uid = 0 GROUP BY pn_optionid DESC";
		$result =& $dbconn->Execute($sql);

		$anonymousvotecount = 0;
		
		list($optID, $anonymousvotecount) = $result->fields;
		$totalvotecount = $totalvotecount + $anonymousvotecount;
	}

	$votecountarray = array();
	$recordcount = 0;

	// Set initial vote id
	$leadingvotecount = 0;
	$leadingvoteid = 0;

    // for ease of backwards compatabilty lets check for a 0 option count
	if ($item['optioncount'] == 0) {
		$item['optioncount'] = pnModGetVar('advanced_polls', 'defaultoptioncount');
	}

	// now lets get all the vote counts
	$sql = "SELECT COUNT($advanced_pollsvotescolumn[optionid]),
						$advanced_pollsvotescolumn[optionid]
			FROM $advanced_pollsvotestable WHERE
			$advanced_pollsvotescolumn[pollid] = '" . (int)pnVarPrepForStore($args['pollid']) . "'
			GROUP BY $advanced_pollsvotescolumn[optionid]";
	$result =& $dbconn->Execute($sql);

	// Check for no rows found, and if so return
	if ($result->EOF) {
		return false;
	}

    for (; !$result->EOF; $result->MoveNext()) {
		list($count, $optionid) = $result->fields; 
		$votecountarray[$optionid] = $count;
	}

	for ($i = 1, $max = $item['optioncount']; $i <= $max; $i++) {
		if (!isset($votecountarray[$i])) {
			$votecountarray[$i] = 0;
		}
		if (($votecountarray[$i] == $leadingvotecount) and ($item['tiebreakalg']) > 0) {
			if ($item['tiebreakalg'] == 1) {
				$leadingvoteid = pnModAPIFunc('advanced_polls', 'user',	'timecountback',
 								array('pollid' => $args['pollid'], 'voteid1' => $leadingvoteid,	'voteid2' => $i));
			}
		}

		if ($votecountarray[$i] > $leadingvotecount) {
			$leadingvotecount = $votecountarray[$i];
			$leadingvoteid = $i;
		}
	}

	// Create the item array
	$item = array('totalvotecount' => $totalvotecount,
				  'leadingvoteid' => $leadingvoteid,
				  'votecountarray' => $votecountarray );

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
	// Argument check
	if (!isset($args['pollid']) || !isset($args['optionid']) || !isset($args['voterank'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// Security check
	if (pnSecAuthAction(0,'advanced_polls::item',"{$args['title']}::{$args['pollid']}",ACCESS_COMMENT)) {
        $args['ip'] = pnServerGetVar('REMOTE_ADDR');
		$args['uid'] = pnUserGetVar('uid');
		$args['time'] = time();
		if (!DBUtil::insertObject($args, 'advanced_polls_votes', 'id')) {
			return LogUtil::registerError (_ADVANCEDPOLLSVOTEFAILED);
		}

		//set cookie to indicate vote made in this poll
		//used only with cookie based voting but set all the time
		//in case admin changes voting regs.
		SessionUtil::setVar("advanced_polls_voted{$args['pollid']}", 1);

		return true;
	}

	return false;
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
		return LogUtil::registerError (_MODARGSERROR);
	}

	// get database connection
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	$advanced_pollsvotestable = $pntable['advanced_polls_votes'];
	$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes_column'];

	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

	$sql = "SELECT SUM($advanced_pollsvotescolumn[time])
			FROM $advanced_pollsvotestable WHERE
			(($advanced_pollsvotescolumn[pollid] = '". (int)pnVarPrepForStore($pollid) . "') AND
			($advanced_pollsvotescolumn[optionid] = '" . (int)pnVarPrepForStore($voteid1) . "'))";
	$result =& $dbconn->Execute($sql);
	list($firstsum) = $result->fields;

	$sql = "SELECT SUM($advanced_pollsvotescolumn[time])
			FROM $advanced_pollsvotestable WHERE
			(($advanced_pollsvotescolumn[pollid] = '" . (int)pnVarPrepForStore($pollid) . "') AND
			($advanced_pollsvotescolumn[optionid] = '" . (int)pnVarPrepForStore($voteid2) . "'))";
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
