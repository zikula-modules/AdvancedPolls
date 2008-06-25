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
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$args['title']}::", ACCESS_ADD)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
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
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_DELETE)) {
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
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$args['title']}::{$args['pollid']}", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	}

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
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
	// Argument check
	if (!isset($args['pollid'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}
	
	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	} else {
		if (!DBUtil::deleteObjectByID('advanced_polls_votes', $args['pollid'], 'pollid')) {
			return LogUtil::registerError (_ADVANCEDPOLLSVOTESRESETFAILED);
		}
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
	// Argument check
	if (!isset($args['pollid'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// Optional arguments.
	if (!isset($args['startnum'])) {
		$args['startnum'] = 1;
	}
	if (!isset($args['numitems'])) {
		$args['numitems'] = -1;
	}
	if ((!isset($args['startnum']))  || (!isset($args['numitems']))) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	if (!isset($args['sortorder'])) {
		$args['sortorder'] = 0;
	}
	if (!isset($args['sortby'])) {
		$args['sortby'] = 1;
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
		return LogUtil::registerError(_MODULENOAUTH);
	} else {
		// get database setup
		$pntable = pnDBGetTables();
		$advanced_pollsvotescolumn = &$pntable['advanced_polls_votes_column'];

		switch ($args['sortby']) {
			case 1:
				$sortstring = " ORDER BY {$advanced_pollsvotescolumn['voteid']}";
				break;
			case 2:
				$sortstring = " ORDER BY {$advanced_pollsvotescolumn['ip']}";
				break;
			case 3:
				$sortstring = " ORDER BY {$advanced_pollsvotescolumn['time']}";
				break;
			case 4:
				$sortstring = " ORDER BY {$advanced_pollsvotescolumn['uid']}";
				break;
			case 5:
				$sortstring = " ORDER BY {$advanced_pollsvotescolumn['voterank']}";
				break;
			case 6:
				$sortstring = " ORDER BY {$advanced_pollsvotescolumn['optionid']}";
				break;
			default:
				$sortstring = '';
		}

		if ($args['sortorder'] == 1 ) {
			$sortstring = $sortstring . ' DESC';
		}

		// get the objects from the db
		$votes = DBUtil::selectObjectArray('advanced_polls_votes', $where, $sortstring);

		// Check for an error with the database code, and if so set an appropriate
		// error message and return
		if ($votes === false) {
			return LogUtil::registerError (_GETFAILED);
		}
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
	// Argument check
	if (!isset($args['pollid'])) {
		return LogUtil::registerError (_MODARGSERROR);
	}

	// The user API function is called.
	$item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

	// check for no such poll return from api function
	if ($item == false) {
		return LogUtil::registerError(_NOSUCHITEM);
	}

	// Security check
	if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_ADD)) {
		return LogUtil::registerError(_MODULENOAUTH);
	} else {
		// The API function is called.
		$pid = pnModAPIFunc('advanced_polls', 'admin', 'create',
			array('title' => $item['title'],
				  'description' => $item['description'],
				  'language' => $item['language'],
				  'opendid' => date("d", $item['opendate']),
				  'openmid' => date("n", $item['opendate']),
				  'openyid' => date("Y", $item['opendate']),
				  'openhid' => date("H", $item['opendate']),
				  'openminid' => date("i", $item['opendate']),
				  'closedid' => date("d", $item['closedate']),
				  'closemid' => date("n", $item['closedate']),
				  'closeyid' => date("Y", $item['closedate']),
				  'closehid' => date("H", $item['closedate']),
				  'closeminid' => date("i", $item['closedate']),
				  'tiebreak' => $item['tiebreakalg'],
				  'voteauthtype' => $item['voteauthtype'],
				  'multipleselect' => $item['multipleselect'],
				  'multipleselectcount' => $item['multipleselectcount'],
				  'recurring' => $item['recurring'],
				  'recurringoffset' => $item['recurringoffset'],
				  'recurringinterval' => $item['recurringinterval'],
				  'optioncount' => $item['optioncount']));

		if ($pid != false) {
			// Once the poll is created we call the modify function to add 
			// the poll options
			$result = pnModAPIFunc('advanced_polls', 'admin', 'update',
				array('pollid' => $pid,
					  'title' => $item['title'],
					  'description' => $item['description'],
					  'optioncount' => $item['optioncount'],
					  'language' => $item['language'],
					  'opendid' => date("d", $item['opendate']),
					  'openmid' => date("n", $item['opendate']),
					  'openyid' => date("Y", $item['opendate']),
					  'openhid' => date("H", $item['opendate']),
					  'openminid' => date("i", $item['opendate']),
					  'closedid' => date("d", $item['closedate']),
					  'closemid' => date("n", $item['closedate']),
					  'closeyid' => date("Y", $item['closedate']),
					  'closehid' => date("H", $item['closedate']),
					  'closeminid' => date("i", $item['closedate']),
					  'tiebreak' => $item['tiebreakalg'],
					  'voteauthtype' => $item['voteauthtype'],
					  'multipleselect' => $item['multipleselect'],
					  'multipleselectcount' => $item['multipleselectcount'],
					  'recurring' => $item['recurring'],
					  'recurringoffset' => $item['recurringoffset'],
					  'recurringinterval' => $item['recurringinterval'],
					  'options' => $item['optionarray']));
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

/**
 * get available admin panel links
 *
 * @author Mark West
 * @return array array of admin links
 */
function advanced_polls_adminapi_getlinks()
{
    $links = array();

    pnModLangLoad('advanced_polls', 'admin');

    if (SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_READ)) {
        $links[] = array('url' => pnModURL('advanced_polls', 'admin', 'view'), 'text' => _ADVANCEDPOLLSVIEW);
    }
    if (SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADD)) {
        $links[] = array('url' => pnModURL('advanced_polls', 'admin', 'new'), 'text' => _ADVANCEDPOLLSNEW);
    }
    if (SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('advanced_polls', 'admin', 'modifyconfig'), 'text' => _MODIFYCONFIG);
    }

    return $links;
}
