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

// defines used in block modifcation form
define('_ADVANCEDPOLLSPECIFIC','Specific Poll');
define('_ADVANCEDPOLLSLATEST','Latest Poll');
if (!defined('_ADVANCEDPOLLSOPENCLOSEBASEDDISPLAY')) {
	define('_ADVANCEDPOLLSOPENCLOSEBASEDDISPLAY', 'Display Block based on Open/Close Dates');
}
define('_ADVANCEDPOLLSDISPLAYRESULTS', 'Display Results Page after Vote');

// defines in block display most are duplicated in the main user interface
// so we must check if they're already defined to prevent E_ALL errors.
if (!defined('_ADVANCEDPOLLSTITLE')) {
	define('_ADVANCEDPOLLSTITLE','Poll to Display');
}
if (!defined('_ADVANCEDPOLLSID')) {
	define('_ADVANCEDPOLLSID','Poll');
}
if (!defined('_ADVANCEDPOLLSADDVOTE')) {
	define('_ADVANCEDPOLLSADDVOTE','Vote');
}
if (!defined('_ADVANCEDPOLLSVOTECOUNT')) {
	define('_ADVANCEDPOLLSVOTECOUNT','Votes');
}
if (!defined('_ADVANCEDPOLLSDETAILEDRESULTS')) {
	define('_ADVANCEDPOLLSDETAILEDRESULTS','Detailed Results');
}
if (!defined('_ADVANCEDPOLLSCURRENTLEADER')) {
	define('_ADVANCEDPOLLSCURRENTLEADER', 'Current Leader');
}
if (!defined('_ADVANCEDPOLLSCLOSEDATE')) {
	define('_ADVANCEDPOLLSCLOSEDATE', 'Close Date');
}
if (!defined('_ADVANCEDPOLLSCHOICE')) {
	define('_ADVANCEDPOLLSCHOICE', 'Choice');
}
if (!defined('_ADVANCEDPOLLSRANKEDCHOICE')) {
	define('_ADVANCEDPOLLSRANKEDCHOICE', 'Choice');
}
if (!defined('_ADVANCEDPOLLSWINNER')) {
	define('_ADVANCEDPOLLSWINNER','Winner');
}
if (!defined('_ADVANCEDPOLLSUSE')) {
	define('_ADVANCEDPOLLSUSE','Poll to Use (Latest and Random override individual poll selection)');
}
if (!defined('_ADVANCEDPOLLSAT')) {
	define('_ADVANCEDPOLLSAT', 'at');
}
?>