<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2010 by Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Argument check
    if (!isset($args['title']) || !isset($args['description']) || !isset($args['optioncount'])) {
        return LogUtil::registerArgsError();
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$args['title']}::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
    }

    if (isset($args['unixopendate'])) {
        // used for duplication a poll
        $args['opendate'] = $args['unixopendate'];
    } else {
        $args['opendate'] = DateUtil::makeTimestamp(DateUtil::buildDatetime($args['startYear'], $args['startMonth'], $args['startDay'], $args['startHour'], $args['startMinute'], 0));
    }

    if (isset($args['unixclosedate'])) {
        // used for duplication a poll
        $args['closedate'] = $args['unixclosedate'];
    } else {
        if (!$args['noclosedate']) {
            $args['closedate'] = DateUtil::makeTimestamp(DateUtil::buildDatetime($args['closeYear'], $args['closeMonth'], $args['closeDay'], $args['closeHour'], $args['closeMinute'], 0));
        } else {
            $args['closedate'] = 0;
        }
    }

    if (!DBUtil::insertObject($args, 'advanced_polls_desc', 'pollid')) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
    }

    // Let any hooks know that we have created a new item.
    pnModCallHooks('item', 'create', $args['pollid'], array('module' => 'advanced_polls'));

    // An item was created, so we clear all cached pages of the items list.
    $renderer = pnRender::getInstance('advanced_polls');
    $renderer->clear_cache('advancedpolls_user_view.htm');

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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // Get the poll
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

    if ($item == false) {
        return LogUtil::registerError (__('Error! No such item found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Delete the object
    if (!DBUtil::deleteObjectByID('advanced_polls_votes', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (__('Error! Deletion attempt failed.', $dom));
    }
    if (!DBUtil::deleteObjectByID('advanced_polls_data', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (__('Error! Deletion attempt failed.', $dom));
    }
    if (!DBUtil::deleteObjectByID('advanced_polls_desc', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (__('Error! Deletion attempt failed.', $dom));
    }

    return true;
}

/**
 * update a poll
 * @param $args['pollid'] the ID of the item
 * @param $args['title'] the name of the poll to be updated
 * @param $args['description'] the name of the poll to be updated
 * @param $args['language'] the number of the item to be updated
 * @param $args['tiebreakalg'] the tiebreak methodlogy to use
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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Argument check
    if (!isset($args['pollid']) || !isset($args['title']) ||
    !isset($args['description']) || !isset($args['optioncount'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such item found.', $dom));
    }

    // Security check

    // Note that at this stage we have two sets of item information, the
    // pre-modification and the post-modification.
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$args['title']}::{$args['pollid']}", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    }

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
    }

    if (isset($args['unixopendate'])) {
        // used for duplication a poll
        $args['opendate'] = $args['unixopendate'];
    } else {
        $args['opendate'] = DateUtil::makeTimestamp(DateUtil::buildDatetime($args['startYear'], $args['startMonth'], $args['startDay'], $args['startHour'], $args['startMinute'], 0));
    }

    if (isset($args['unixclosedate'])) {
        // used for duplication a poll
        $args['closedate'] = $args['unixclosedate'];
    } else {
        if (!$args['noclosedate']) {
            $args['closedate'] = DateUtil::makeTimestamp(DateUtil::buildDatetime($args['closeYear'], $args['closeMonth'], $args['closeDay'], $args['closeHour'], $args['closeMinute'], 0));
        } else {
            $args['closedate'] = 0;
        }
    }

    // update the object
    if (!DBUtil::updateObject($args, 'advanced_polls_desc', '', 'pollid')) {
        return LogUtil::registerError (__('Error! Update attempt failed.', $dom));
    }

    // first delete the poll options before reinserting them
    if (!DBUtil::deleteObjectByID('advanced_polls_data', $args['pollid'], 'pollid')) {
        return LogUtil::registerError (__('Error! Deletion attempt failed.', $dom));
    }
    for ($count = 1; $count <= $args['optioncount']; $count++) {
        $items[] = array('pollid' => $args['pollid'],
                         'optiontext' => $args['options'][$count]['optiontext'],
                         'optioncolour' => $args['options'][$count]['optioncolour'],
                         'optionid' => $count);
    }
    if (!DBUtil::insertObjectArray($items, 'advanced_polls_data')) {
        return LogUtil::registerError (__('Error! Update attempt failed.', $dom));
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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

    // check for no such poll return from api function
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such item found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    } else {
        if (!DBUtil::deleteObjectByID('advanced_polls_votes', $args['pollid'], 'pollid')) {
            return LogUtil::registerError (__('Error! Vote reset failed.', $dom));
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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // Optional arguments.
    if (!isset($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['numitems'])) {
        $args['numitems'] = -1;
    }
    if ((!isset($args['startnum']))  || (!isset($args['numitems']))) {
        return LogUtil::registerArgsError();
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
        return LogUtil::registerError(__('Error! No such item found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    } else {
        // get database setup
        $pntable = pnDBGetTables();
        $votescolumn = &$pntable['advanced_polls_votes_column'];

        switch ($args['sortby']) {
            case 1:
                $sortstring = " ORDER BY {$votescolumn['voteid']}";
                break;
            case 2:
                $sortstring = " ORDER BY {$votescolumn['ip']}";
                break;
            case 3:
                $sortstring = " ORDER BY {$votescolumn['time']}";
                break;
            case 4:
                $sortstring = " ORDER BY {$votescolumn['uid']}";
                break;
            case 5:
                $sortstring = " ORDER BY {$votescolumn['voterank']}";
                break;
            case 6:
                $sortstring = " ORDER BY {$votescolumn['optionid']}";
                break;
            default:
                $sortstring = '';
        }

        if ($args['sortorder'] == 1 ) {
            $sortstring = $sortstring . ' DESC';
        }

        $where = "WHERE $votescolumn[pollid] = '" . DataUtil::formatForStore($args['pollid']) . "'";

        // get the objects from the db
        $votes = DBUtil::selectObjectArray('advanced_polls_votes', $where, $sortstring, $args['startnum']-1, $args['numitems']);

        // Check for an error with the database code, and if so set an appropriate
        // error message and return
        if ($votes === false) {
            return LogUtil::registerError (__('Error! Could not load items.', $dom));
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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

    // check for no such poll return from api function
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such item found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "{$item['title']}::{$args['pollid']}", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    } else {
        // The API function is called.
        $pid = pnModAPIFunc('advanced_polls', 'admin', 'create',
        array('title'               => $item['title'],
              'urltitle'            => $item['urltitle'],
              'description'         => $item['description'],
              'language'            => $item['language'],
              'unixopendate'        => $item['opendate'],
              'unixclosedate'       => $item['closedate'],
              'tiebreakalg'         => $item['tiebreakalg'],
              'voteauthtype'        => $item['voteauthtype'],
              'multipleselect'      => $item['multipleselect'],
              'multipleselectcount' => $item['multipleselectcount'],
              'recurring'           => $item['recurring'],
              'recurringoffset'     => $item['recurringoffset'],
              'recurringinterval'   => $item['recurringinterval'],
              'optioncount'         => $item['optioncount']));

        if ($pid != false) {
            // Once the poll is created we call the modify function to add
            // the poll options
            $result = pnModAPIFunc('advanced_polls', 'admin', 'update',
            array('pollid'              => $pid,
                  'title'               => $item['title'],
                  'urltitle'            => $item['urltitle']. $pid,
                  'description'         => $item['description'],
                  'optioncount'         => $item['optioncount'],
                  'language'            => $item['language'],
                  'unixopendate'        => $item['opendate'],
                  'unixclosedate'       => $item['closedate'],
                  'tiebreakalg'         => $item['tiebreakalg'],
                  'voteauthtype'        => $item['voteauthtype'],
                  'multipleselect'      => $item['multipleselect'],
                  'multipleselectcount' => $item['multipleselectcount'],
                  'recurring'           => $item['recurring'],
                  'recurringoffset'     => $item['recurringoffset'],
                  'recurringinterval'   => $item['recurringinterval'],
                  'options'             => $item['optionarray']));
        } else {
            $result = false;
        }

        // The return value of the function is checked
        if ($result = false) {
            LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
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
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    $links = array();

    if (SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_READ)) {
        $links[] = array('url' => pnModURL('advanced_polls', 'admin', 'view'), 'text' => __('View polls', $dom));
    }
    if (SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADD)) {
        $links[] = array('url' => pnModURL('advanced_polls', 'admin', 'new'), 'text' => __('Create new poll', $dom));
    }
    if (SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_ADMIN)) {
        $links[] = array('url' => pnModURL('advanced_polls', 'admin', 'modifyconfig'), 'text' => __('Settings', $dom));
    }

    return $links;
}
