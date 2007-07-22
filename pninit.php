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
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_init() 
{
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
	// Create the table - the formatting here is not mandatory, but it does
	// make the SQL statement relatively easy to read.  Also, separating out
	// the SQL statement from the Execute() command allows for simpler
	// debug operation if it is ever needed
	//    ------------------
	$advpollsvotestable = $pntable['advancedpollsvotes'];
	$advpollsvotescolumn = &$pntable['advanced_polls_votes'];
	$sql = "CREATE TABLE $advpollsvotestable (
		$advpollsvotescolumn[pn_voteid] int(11) NOT NULL auto_increment,
		$advpollsvotescolumn[pn_ip] varchar(20) NOT NULL default '',
		$advpollsvotescolumn[pn_time] varchar(14) NOT NULL default '',
		$advpollsvotescolumn[pn_uid] int(11) NOT NULL default '0',
		$advpollsvotescolumn[pn_voterank] int(4) NOT NULL default '0',
		$advpollsvotescolumn[pn_pollid] int(11) NOT NULL default '0',
		$advpollsvotescolumn[pn_optionid] int(11) NOT NULL default '0',
		PRIMARY KEY (pn_voteid))";
	 
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _CREATETABLE1FAILED);
		return false;
	}
	 
	//    ------------------
	$advpollsdatatable = $pntable['advancedpollsdata'];
	$advpollsdatacolumn = &$pntable['advanced_polls_data'];
	$sql = "CREATE TABLE $advpollsdatatable (
		$advpollsdatacolumn[pn_pollid] int(11) NOT NULL default '0',
		$advpollsdatacolumn[pn_optiontext] varchar(255) NOT NULL default '',
		$advpollsdatacolumn[pn_optionid] int(11) NOT NULL default '0',
		$advpollsdatacolumn[pn_optioncolour] varchar(7) NOT NULL default '')";
	$dbconn->Execute($sql);
	 
	// Check database result
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _CREATETABLE3FAILED);
		return false;
	}
	 
	//    ------------------
	$advpollsdesctable = $pntable['advancedpollsdesc'];
	$advpollsdesccolumn = &$pntable['advanced_polls_desc'];
	$sql = "CREATE TABLE $advpollsdesctable (
		$advpollsdesccolumn[pn_pollid] int(11) NOT NULL auto_increment,
		$advpollsdesccolumn[pn_title] varchar(100) NOT NULL default '',
		$advpollsdesccolumn[pn_description] text,
		$advpollsdesccolumn[pn_optioncount] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_opendate] int(16) NOT NULL default '0',
		$advpollsdesccolumn[pn_closedate] int(16) NOT NULL default '0',
		$advpollsdesccolumn[pn_recurring] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_recurringoffset] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_recurringinterval] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_multipleselect] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_multipleselectcount] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_voteauthtype] int(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_tiebreakalg] mediumint(4) NOT NULL default '0',
		$advpollsdesccolumn[pn_language] varchar(30) NOT NULL default '',
		$advpollsdesccolumn[pn_votingmethod] int(4) NOT NULL default '0',
		PRIMARY KEY(pn_pollid))";
	$dbconn->Execute($sql);
	 
	// Check database result
	if ($dbconn->ErrorNo()  != 0) {
		pnSessionSetVar('errormsg', _CREATETABLE4FAILED);
		return false;
	}

	// Set up an initial value for a module variable.  Note that all module
	// variables should be initialised with some value in this way rather
	// than just left blank, this helps the user-side code and means that
	// there doesn't need to be a check to see if the variable is set in
	// the rest of the code as it always will be
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
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_upgrade($oldversion) 
{
	// Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
	// return arrays but we handle them differently.  For pnDBGetConn()
	// we currently just want the first item, which is the official
	// database handle.  For pnDBGetTables() we want to keep the entire
	// tables array together for easy reference later on
	// This code could be moved outside of the switch statement if
	// multiple upgrades need it
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();

	// It's good practice to name the table and column definitions you
	// are getting - $table and $column don't cut it in more complex
	// modules
	// This code could be moved outside of the switch statement if
	// multiple upgrades need it
	$advpollsdatatable = $pntable['advancedpollsdata'];
	$advpollsdatacolumn = &$pntable['advanced_polls_data'];
	$advpollsdesctable = $pntable['advancedpollsdesc'];
	$advpollsdesccolumn = &$pntable['advanced_polls_desc'];

	// Upgrade dependent on old version number
	switch($oldversion) {
		case 1.0:
		
			// Version 1.0 Didn't have the Module variables
			pnModSetVar('advanced_polls', 'admindateformat', 'r');
			pnModSetVar('advanced_polls', 'userdateformat', 'r');
			pnModSetVar('advanced_polls', 'usereversedns', 0);
			pnModSetVar('advanced_polls', 'scalingfactor', 4);
			
			// At the end of the successful completion of this function we
			// recurse the upgrade to handle any other upgrades that need
			// to be done.  This allows us to upgrade from any version to
			// the current version with ease
			return advanced_polls_upgrade(1.1);

		case 1.1:

			// Version 1.1 didn't have a 'description' or 'optioncolour' fields, 
			// these were added in version 1.2
	
			// Add a column to the table - the formatting here is not
			// mandatory, but it does make the SQL statement relatively easy
			// to read.  Also, separating out the SQL statement from the
			// Execute() command allows for simpler debug operation if it is
			// ever needed
			$sql = "ALTER TABLE $advpollsdatatable
					ADD $advpollsdatacolumn[pn_optioncolour] varchar(7) NOT NULL default ''";
			$dbconn->Execute($sql);
	
			// Check for an error with the database code, and if so set an
			// appropriate error message and return
			if ($dbconn->ErrorNo() != 0) {
				pnSessionSetVar('errormsg', _UPDATETABLEFAILED);
				return false;
			}
	
			// Add a column to the table - the formatting here is not
			// mandatory, but it does make the SQL statement relatively easy
			// to read.  Also, separating out the SQL statement from the
			// Execute() command allows for simpler debug operation if it is
			// ever needed
			$sql = "ALTER TABLE $advpollsdesctable
					ADD $advpollsdesccolumn[pn_description] text";
			$dbconn->Execute($sql);
	
			// Check for an error with the database code, and if so set an
			// appropriate error message and return
			if ($dbconn->ErrorNo() != 0) {
				pnSessionSetVar('errormsg', _UPDATETABLEFAILED);
				return false;
			}
	
			// Add a column to the table - the formatting here is not
			// mandatory, but it does make the SQL statement relatively easy
			// to read.  Also, separating out the SQL statement from the
			// Execute() command allows for simpler debug operation if it is
			// ever needed
			$sql = "ALTER TABLE $advpollsdesctable
					ADD $advpollsdesccolumn[pn_optioncount] int(4) NOT NULL default '0'";
			$dbconn->Execute($sql);
	
			// Check for an error with the database code, and if so set an
			// appropriate error message and return
			if ($dbconn->ErrorNo() != 0) {
				pnSessionSetVar('errormsg', _UPDATETABLEFAILED);
				return false;
			}
	
			// Add additional module variables in this version
			pnModSetVar('advanced_polls', 'adminitemsperpage', 25);
			pnModSetVar('advanced_polls', 'useritemsperpage', 25);
			pnModSetVar('advanced_polls', 'defaultcolour', '#000000');
			pnModSetVar('advanced_polls', 'defaultoptioncount', '12');
	
			// At the end of the successful completion of this function we
			// recurse the upgrade to handle any other upgrades that need
			// to be done.  This allows us to upgrade from any version to
			// the current version with ease
			return advanced_polls_upgrade(1.5);
		
		case 1.5:
			// Extend length of field - the formatting here is not
			// mandatory, but it does make the SQL statement relatively easy
			// to read.  Also, separating out the SQL statement from the
			// Execute() command allows for simpler debug operation if it is
			// ever needed
			$sql = "ALTER TABLE $advpollsdatatable
					CHANGE $advpollsdatacolumn[pn_optiontext] $advpollsdatacolumn[pn_optiontext] varchar(255) NOT NULL";
			$dbconn->Execute($sql);
	
			// Check for an error with the database code, and if so set an
			// appropriate error message and return
			if ($dbconn->ErrorNo() != 0) {
				pnSessionSetVar('errormsg', _UPDATETABLEFAILED);
				return false;
			}

			// At the end of the successful completion of this function we
			// recurse the upgrade to handle any other upgrades that need
			// to be done.  This allows us to upgrade from any version to
			// the current version with ease
			return advanced_polls_upgrade(1.51);
	}

	// Update successful
	return true;
	
}
 
/**
* delete the template module
* This function is only ever called once during the lifetime of a particular
* module instance
* @returns bool
* @return true on success, false on failure
* @author Mark West <mark@markwest.me.uk>
* @copyright (C) 2002-2004 by Mark West
* @since 1.0
* @version 1.1
*/
function advanced_polls_delete() 
{
	// Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
	// return arrays but we handle them differently.  For pnDBGetConn()
	// we currently just want the first item, which is the official
	// database handle.  For pnDBGetTables() we want to keep the entire
	// tables array together for easy reference later on
	$dbconn =& pnDBGetConn(true);
	$pntable =& pnDBGetTables();
	 
	// Drop the table - for such a simple command the advantages of separating
	// out the SQL statement from the Execute() command are minimal, but as
	// this has been done elsewhere it makes sense to stick to a single method
	//    ------------------
	// Delete tables
	$sql = "DROP TABLE $pntable[advancedpollsvotes]";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		// Report failed deletion attempt
		return false;
	}
	//    ------------------
	// Delete tables
	$sql = "DROP TABLE $pntable[advancedpollsdata]";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		// Report failed deletion attempt
		return false;
	}
	//    ------------------
	// Delete tables
	$sql = "DROP TABLE $pntable[advancedpollsdesc]";
	$dbconn->Execute($sql);
	 
	// Check for an error with the database code, and if so set an
	// appropriate error message and return
	if ($dbconn->ErrorNo()  != 0) {
		// Report failed deletion attempt
		return false;
	}
	
	// Delete any module variables
	pnModDelVar('advanced_polls');

	// Deletion successful
	return true;
}

