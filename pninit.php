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
 * Initialise the Advanced Polls module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return bool true on success, false on failure
 * @since 1.0
*/
function advanced_polls_init() 
{
    // create tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::createTable($table)) {
            return false;
        }
    }

	// Set up an initial value for each module variable
	pnModSetVar('advanced_polls', 'admindateformat', '_DATETIMEBRIEF');
	pnModSetVar('advanced_polls', 'userdateformat', '_DATETIMEBRIEF');
	pnModSetVar('advanced_polls', 'usereversedns', 0);
	pnModSetVar('advanced_polls', 'scalingfactor', 4);
	pnModSetVar('advanced_polls', 'adminitemsperpage', 25);
	pnModSetVar('advanced_polls', 'useritemsperpage', 25);
    pnModSetVar('advanced_polls', 'defaultcolour', '#000000');
    pnModSetVar('advanced_polls', 'defaultoptioncount', '12');
											 
	// Initialisation successful
	return true;
}
 
/**
 * upgrade the template module from an old version
 * This function can be called multiple times
 *
 * @return bool true on success, false on failure
 * @since 1.0
*/
function advanced_polls_upgrade($oldversion) 
{
    // update tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::changeTable($table)) {
            return false;
        }
    }

	// Upgrade dependent on old version number
	switch($oldversion) {
		case 1.0:
			// Version 1.0 Didn't have the Module variables
			pnModSetVar('advanced_polls', 'admindateformat', 'r');
			pnModSetVar('advanced_polls', 'userdateformat', 'r');
			pnModSetVar('advanced_polls', 'usereversedns', 0);
			pnModSetVar('advanced_polls', 'scalingfactor', 4);
			return advanced_polls_upgrade(1.1);
		case 1.1:
			// Add additional module variables in this version
			pnModSetVar('advanced_polls', 'adminitemsperpage', 25);
			pnModSetVar('advanced_polls', 'useritemsperpage', 25);
			pnModSetVar('advanced_polls', 'defaultcolour', '#000000');
			pnModSetVar('advanced_polls', 'defaultoptioncount', '12');
			return advanced_polls_upgrade(1.5);
		case 1.5:
            // all changes in this release are covered by the table change
			return advanced_polls_upgrade(1.51);
	}

	// Update successful
	return true;

}
 
/**
 * delete the template module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return bool true on success, false on failure
 * @since 1.0
*/
function advanced_polls_delete() 
{
    // delete tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::dropTable($table)) {
            return false;
        }
    }

	// Delete any module variables
	pnModDelVar('advanced_polls');

	// Deletion successful
	return true;
}
