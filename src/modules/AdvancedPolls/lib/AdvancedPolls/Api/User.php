<?php

class Advanced_Polls_User_Api extends Zikula_AbstractApi {

/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West, Carsten Volmer
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Get all poll items
 * @param $args['startnum'] starting poll id
 * @param $args['numitems'] number of polls to get
 * @param $args['checkml'] flag to check ml status
 * @param $args['desc'] array title key name
 * @return mixed array of items, or false on failure
 */
public function getall($args)
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
        return LogUtil::registerArgsError();
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
        $queryargs[] = "($pollscolumn[language]='" . DataUtil::formatForStore(ZLanguage::getLanguageCode()) . "' OR $pollscolumn[language]='')";
    }

    $where = null;
    if (count($queryargs) > 0) {
        $where = ' WHERE ' . implode(' AND ', $queryargs);
    }

    // define the permission filter to apply
    $permFilter = array(array('realm'           => 0,
                              'component_left'  => 'advanced_polls',
                              'component_right' => 'item',
                              'instance_left'   => 'title',
                              'instance_right'  => 'pollid',
                              'level'           => ACCESS_READ));

    // get the objects from the db
    $items = DBUtil::selectObjectArray('advanced_polls_desc', $where, 'pollid', $args['startnum']-1, $args['numitems'], '', $permFilter, $args['catFilter']);

    // Check for an error with the database code, and if so set an appropriate
    // error message and return
    if ($items === false) {
        return LogUtil::registerError (__('Error! Could not load polls.', $dom));
    }

    // need to do this here as the category expansion code can't know the
    // root category which we need to build the relative path component
    if ($items && isset($args['catregistry']) && $args['catregistry']) {
        if (!($class = Loader::loadClass ('CategoryUtil'))) {
            pn_exit (__f('Error! Unable to load class [%s]', array('s' => 'CategoryUtil'), $dom));
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
 */
public function get($args)
{
    // optional arguments
    if (isset($args['objectid'])) {
        $args['pollid'] = $args['objectid'];
    }

    // Argument check
    if ((!isset($args['pollid']) || !is_numeric($args['pollid'])) && !isset($args['title'])) {
        return LogUtil::registerArgsError();
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::', '::', ACCESS_OVERVIEW)) {
        return false;
    }

    // define the permission filter to apply
    $permFilter = array(array('realm'           => 0,
                              'component_left'  => 'advanced_polls',
                              'component_right' => 'item',
                              'instance_left'   => 'title',
                              'instance_right'  => 'pollid',
                              'level'           => ACCESS_READ));

    if (isset($args['pollid']) && is_numeric($args['pollid'])) {
        $poll = DBUtil::selectObjectByID('advanced_polls_desc', $args['pollid'], 'pollid', '', $permFilter);
    } else {
        $poll = DBUtil::selectObjectByID('advanced_polls_desc', $args['title'], 'urltitle', '', $permFilter);
    }

    $pntable = pnDBGetTables();
    $datacolumn  = $pntable['advanced_polls_data_column'];
    $where = "$datacolumn[pollid]='" . (int)DataUtil::formatForStore($poll['pollid']) . "'";
    $poll['options'] = DBUtil::selectObjectArray('advanced_polls_data', $where, 'optionid');

    // pad the array to the correct number of poll options
    if (count($poll['options']) < $poll['optioncount']) {
        for($counter = 0; $counter < $poll['optioncount']; $counter++) {
            if (!isset($poll['options'][$counter])) {
                $poll['options'][$counter] = array('voteid' => $counter, 'optiontext' => '', 'optioncolour' => '');
            }
        }
    }

    // Return the item array
    return $poll;
}

/**
 * Utility function to count the number of items held by this module
 * @param $args['checkml'] flag to check ml status
 * @return integer number of items held by this module
 */
public function countitems($args)
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

    $pntable = pnDBGetTables();
    $desccolumn = $pntable['advanced_polls_desc_column'];

    // Check if we is an ML situation
    $querylang = '';
    if ($checkml && pnConfigGetVar('multilingual') == 1) {
        $querylang = "WHERE ($desccolumn[language]='" . DataUtil::formatForStore(ZLanguage::getLanguageCode()) . "'
            OR $desccolumn[language]=''
            OR $desccolumn[language] IS NULL)";
    }

    // Return the number of items
    return DBUtil::selectObjectCount('advanced_polls_desc', $querylang, 'pollid', false, $args['catFilter']);
}

/**
 * Check if poll is open
 * @param $args['pollid'] id of example item to get
 * @return bool false if closed, true if open
 */
public function isopen($args)
{
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

    // no such item is db
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such poll found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
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
 * Check if user has voted in poll
 * @param $args['pollid'] id of poll item to get
 * @return bool true on vote allowed, false on vote not allowed
 */
public function isvoteallowed($args)
{
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));

    // no such item in db
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such poll found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_READ)) {
        return LogUtil::registerPermissionError();
    }
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_COMMENT)) {
        // Here we don't set an error as this indicates that the user can't vote in this poll
        return false;
    }

    // get voting authorisation from item array
    $voteauthtype = $item['voteauthtype'];

    switch ($voteauthtype) {
        case 0: //Legacy - should not be used
        case 1: //Free voting
            // voting always allowed
            return true;
        case 2: //UID Voting
            // // extract user id from session variables
            $uid = pnUserGetVar('uid');
            // get all the matching votes
            $pntable = pnDBGetTables();
            $votescolumn  = $pntable['advanced_polls_votes_column'];
            $where = "$votescolumn[uid]='" . (int)DataUtil::formatForStore($uid) . "' AND $votescolumn[pollid]='" . (int)DataUtil::formatForStore($args['pollid']) . "'";
            $votes = DBUtil::selectObjectCount('advanced_polls_votes', $where);

            if ($votes == 0) {
                return true;
            } else {
                return false;
            }
        case 3: //Cookie voting
            // check for existance of session variable (cookie)
            // if set then vote is invalid otherwise set session variable
            // and return valid
            if (SessionUtil::getVar("advanced_polls_voted{$args['pollid']}")) {
                return false;
            } else {
                return true;
            }
        case 4: //IP address voting
            // extract ip from http headers
            $ip = pnServerGetVar('REMOTE_ADDR');

            // get all the matching votes
            $pntable = pnDBGetTables();
            $votescolumn  = $pntable['advanced_polls_votes_column'];
            $where = "$votescolumn[ip]='" . DataUtil::formatForStore($ip) . "' AND $votescolumn[pollid]='" . (int)DataUtil::formatForStore($args['pollid']) . "'";
            $votes = DBUtil::selectObjectCount('advanced_polls_votes', $where);

            //If there are no rows back from this query then this uid can vote
            if ($votes == 0) {
                return true;
            } else {
                return false;
            }
        case 5: //Cookie + IP address voting
            // possibly remove this voting style
            return true;
        default: //any other option - should never occur
            return LogUtil::registerError(__('Error! No poll authorisation method.', $dom));
    }
}

/**
 * Reset polls votes if poll is recurring poll
 * @param $args['pollid'] id of example item to get
 * @return bool true on reset success, false on vote failure
 */
public function resetrecurring($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($pollid)) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    // check for no such poll return from api function
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such poll found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_READ)) {
        return LogUtil::registerPermissionError();
    }

    // convert recurring offset into unix timestamp format
    $offset = $item['recurringoffset'] * 60 * 60;
    $closetimewithoffset = $item['closedate'] + $offset;

    // if this poll is currently closed and poll it set to reoccur
    // then update relevant db tables
    // Doesn't call IsPollOpen API as this checks for both before
    // poll open date and after poll close date
    // We are only insterest in Poll close date
    if (($closetimewithoffset < time()) and ($item['recurring'] == 1)) {

        if (!DBUtil::deleteObjectById('advanced_polls_votes', $pollid, 'pollid')) {
            return LogUtil::registerError (__('Error! Reseting the votes of a recurring poll failed.', $dom));
        }

        // set new opening and closing times
        // calculate recurrance interval in seconds from db value in days
        $recurranceinterval = $item['recurringinterval'] * 24 * 60 * 60;

        //new open is close time with offset calculated earlier in this function
        $newopentime = $closetimewithoffset;
        $newclosetime = $item['closedate'] + $recurranceinterval;

        // update poll close and open times
        $obj = array('pollid'    => $pollid,
                     'opendate'  => $newopentime,
                     'closedate' => $newclosetime);

        if (!DBUtil::updateObject($obj, 'advanced_polls_desc', '', 'pollid')) {
            return LogUtil::registerError (__('Error! Reseting the dates of a recurring poll failed.', $dom));
        }

    }
    return true;
}

/**
 * Get counts of votes in a poll leading vote and total vote count
 * @param $args['pollid'] id of example item to get
 * @return mixed voting array on success, false on failure
 */
public function pollvotecount($args)
{
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['pollid']));
    if ($item == false) {
        return LogUtil::registerError(__('Error! No such poll found.', $dom));
    }

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$args[pollid]", ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    $totalvotecount = DBUtil::selectObjectCountByID('advanced_polls_votes', $args['pollid'], 'pollid');

    $votecountarray = array();
    $recordcount = 0;

    // Set initial vote id
    $leadingvotecount = 0;
    $leadingvoteid = 0;

    // for ease of backwards compatabilty lets check for a 0 option count
    if ($item['optioncount'] == 0) {
        $item['optioncount'] = pnModGetVar('advanced_polls', 'defaultoptioncount');
    }

    // get database tables
    $pntable = pnDBGetTables();
    $votestable = $pntable['advanced_polls_votes'];
    $votescolumn = &$pntable['advanced_polls_votes_column'];

    // now lets get all the vote counts
    $sql = "SELECT COUNT($votescolumn[optionid]) as optioncount, $votescolumn[optionid] as optionid
            FROM $votestable
            WHERE $votescolumn[pollid] = '" . (int)DataUtil::formatForStore($args['pollid']) . "'
            GROUP BY $votescolumn[optionid]";

    $res = DBUtil::executeSQL($sql);
    $colarray = array('optioncount', 'optionid');
    $result = DBUtil::marshallObjects($res, $colarray);

    if (is_array($result) && !empty($result)) {
        foreach ($result as $row) {
            $votecountarray[$row['optionid']] = $row['optioncount'];
        }
    }

    for ($i = 1, $max = $item['optioncount']; $i <= $max; $i++) {
        if (!isset($votecountarray[$i])) {
            $votecountarray[$i] = 0;
        }
        if (($votecountarray[$i] == $leadingvotecount) and ($item['tiebreakalg']) > 0) {
            if ($item['tiebreakalg'] == 1) {
                $leadingvoteid = pnModAPIFunc('advanced_polls', 'user', 'timecountback',
                array('pollid'  => $args['pollid'],
                      'voteid1' => $leadingvoteid,
                      'voteid2' => $i));
            }
        }

        if ($votecountarray[$i] > $leadingvotecount) {
            $leadingvotecount = $votecountarray[$i];
            $leadingvoteid = $i;
        }
    }

    // Create the item array
    $item = array('totalvotecount' => $totalvotecount,
                  'leadingvoteid'  => $leadingvoteid,
                  'votecountarray' => $votecountarray);

    // Return the item array
    return $item;
}

/**
 * Adds vote to db
 * @param $args['pollid'] id of example item to get
 * @param $args['voteid'] poll item to register vote for
 * @param $args['voterank'] ranking of vote in multiple select polls
 * @return bool true on success, false on failure
 */
public function addvote($args)
{
    // Argument check
    if (!isset($args['pollid']) || !isset($args['optionid']) || !isset($args['voterank'])) {
        return LogUtil::registerArgsError();
    }

    // Security check
    if (SecurityUtil::checkPermission('advanced_polls::item',"{$args['title']}::{$args['pollid']}",ACCESS_COMMENT)) {
        $args['ip'] = pnServerGetVar('REMOTE_ADDR');
        $args['uid'] = pnUserGetVar('uid');
        $args['time'] = time();
        if (!DBUtil::insertObject($args, 'advanced_polls_votes', 'id')) {
            return LogUtil::registerError (__('Error! Failed to register vote.', $dom));
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
 */
public function timecountback($args)
{
    // Get arguments from argument array
    extract($args);

    // Argument check
    if (!isset($pollid) || !isset($voteid1) || !isset($voteid2)) {
        return LogUtil::registerArgsError();
    }

    // get database tables
    $pntable = pnDBGetTables();
    $votestable = $pntable['advanced_polls_votes'];
    $votescolumn = &$pntable['advanced_polls_votes_column'];

    $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $pollid));

    // Security check
    if (!SecurityUtil::checkPermission('advanced_polls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
        return LogUtil::registerPermissionError();
    }

    $sql = "SELECT SUM($votescolumn[time])
            FROM $votestable
            WHERE (($votescolumn[pollid] = '". (int)DataUtil::formatForStore($pollid) . "')
            AND ($votescolumn[optionid] = '" . (int)DataUtil::formatForStore($voteid1) . "'))";

    $firstsum = DBUtil::executeSQL($sql);

    $sql = "SELECT SUM($votescolumn[time])
            FROM $votestable
            WHERE (($votescolumn[pollid] = '" . (int)DataUtil::formatForStore($pollid) . "')
            AND ($votescolumn[optionid] = '" . (int)DataUtil::formatForStore($voteid2) . "'))";

    $secondsum = DBUtil::executeSQL($sql);

    if ($firstsum < $secondsum) {
        return $voteid1;
    } else {
        return $voteid2;
    }
}

/**
 * Gets id of last poll to close
 * @return int id of last poll to close, 0 if no closed polls
 */
public function getlastclosed($args)
{
    // The API function is called.
    $items = pnModAPIFunc('advanced_polls', 'user', 'getall');

    // work out which poll has closed most recently
    $lastclosed = 0;
    $lastcloseddate = 0;
    foreach ($items as $item) {
        if ($item['opendate'] < time() && $item['closedate'] < time() && $item['closedate'] != 0 && $item['closedate'] >= $lastcloseddate) {
            $lastclosed = $item['pollid'];
            $lastcloseddate = $item['closedate'];
        }
    }

    return $lastclosed;
}

/**
 * Gets a random poll id
 * @return int id of poll
 */
public function getrandom() {
    // seed with microseconds
    function make_seed() {
        list($usec, $sec) = explode(' ', microtime());
        return (float) $sec + ((float) $usec * 100000);
    }
    srand(make_seed());

    // The API function is called.
    $items = pnModAPIFunc('advanced_polls', 'user', 'getall');

    $randomitemid = array_rand($items , 1);
    $randomitem = $items[$randomitemid];
    $pollid = $randomitem['pollid'];

    return $pollid;
}

/**
 * Form custom url string
 *
 * @return string custom url string
 */
public function encodeurl($args)
{
    // check we have the required input
    if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
        return LogUtil::registerArgsError();
    }

    // create an empty string ready for population
    $vars = '';

    // view function
    if ($args['func'] == 'view' && isset($args['args']['cat'])) {
        $vars = substr($args['args']['cat'], 1);
    }

    // for the display function use either the title (if present) or the page id
    if ($args['func'] == 'display' || $args['func'] == 'results') {
        // check for the generic object id parameter
        if (isset($args['args']['objectid'])) {
            $args['args']['pollid'] = $args['args']['objectid'];
        }
        // get the item
        if (isset($args['args']['pollid'])) {
            $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $args['args']['pollid']));
        } else {
            $item = pnModAPIFunc('advanced_polls', 'user', 'get', array('title' => $args['args']['title']));
        }
        $vars = $item['urltitle'];
        if (isset($args['args']['page']) && $args['args']['page'] != 1) {
            $vars .= '/page/'.$args['args']['page'];
        }
    }
    // don't display the function name if either displaying an page or the normal overview
    if ($args['func'] == 'main' || $args['func'] == 'display') {
        $args['func'] = '';
    }

    // construct the custom url part
    if (empty($args['func']) && empty($vars)) {
        return $args['modname'] . '/';
    } elseif (empty($args['func'])) {
        return $args['modname'] . '/' . $vars . '/';
    } elseif (empty($vars)) {
        return $args['modname'] . '/' . $args['func'] . '/';
    } else {
        return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
    }
}

/**
 * Decode the custom url string
 *
 * @return bool true if successful, false otherwise
 */
public function decodeurl($args)
{
    // check we actually have some vars to work with...
    if (!isset($args['vars'])) {
        return LogUtil::registerArgsError();
    }

    // define the available user functions
    $funcs = array('main', 'view', 'display', 'results', 'vote');
    // set the correct function name based on our input
    if (empty($args['vars'][2])) {
        pnQueryStringSetVar('func', 'main');
    } elseif (!in_array($args['vars'][2], $funcs)) {
        pnQueryStringSetVar('func', 'display');
        $nextvar = 2;
    } else {
        pnQueryStringSetVar('func', $args['vars'][2]);
        $nextvar = 3;
    }

    $func = FormUtil::getPassedValue('func');

    // add the category info
    if ($func == 'view') {
        pnQueryStringSetVar('cat', (string)$args['vars'][$nextvar]);
    }

    // identify the correct parameter to identify the page
    if ($func == 'display' || $func == 'results') {
        // get rid of unused vars
        $args['vars'] = array_slice($args['vars'], $nextvar);
        $nextvar = 0;
        if (is_numeric($args['vars'][$nextvar])) {
            pnQueryStringSetVar('pollid', $args['vars'][$nextvar]);
        } else {
            pnQueryStringSetVar('title', $args['vars'][$nextvar]);
        }
    }

    return true;
}
}