<?php 
// ----------------------------------------------------------------------
// Advanced Polls Module for the POST-NUKE Content Management System
// Copyright (C) 2002-2004 by Mark West
// http://www.markwest.me.uk/
// ----------------------------------------------------------------------
//
/**
* Advanced Polls Module Poll Block Language file - English
* @author Mark West <mark@markwest.me.uk> 
* @link http://www.markwest.me.uk Advanced Polls Support Site
* @copyright (C) 2002-2004 by Mark West
*/

// defines used in block modifcation form some are duplicated in the main user interface
// so we must check if they're already defined to prevent E_ALL errors.
define('_ADVANCEDPOLLSWARNINDIVIDUALSELECTION', 'Individual Selection');
define('_ADVANCEDPOLLSWARNLATEST', 'Latest Poll');
define('_ADVANCEDPOLLSWARNRANDOM', 'Random Poll');
define('_ADVANCEDPOLLSBACKGROUNDCOLOR', 'Background Color');
if (!defined('_ADVANCEDPOLLSID')) {
	define('_ADVANCEDPOLLSID','Poll');
}
if (!defined('_ADVANCEDPOLLSOPENCLOSEBASEDDISPLAY')) {
	define('_ADVANCEDPOLLSOPENCLOSEBASEDDISPLAY', 'Display Block based on Open/Close Dates');
}
if (!defined('_ADVANCEDPOLLSUSE')) {
	define('_ADVANCEDPOLLSUSE','Poll to Use (Latest and Random override individual poll selection)');
}

// defines in block display 
define('_ADVANCEDPOLLSWARN', 'You have not voted in the poll');
?>