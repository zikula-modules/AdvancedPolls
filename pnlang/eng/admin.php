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

define('_ADVANCEDPOLLSCANCELDELETE', 'Cancel deletion');
define('_ADVANCEDPOLLSCONFIRMDELETE', 'Confirm deletion of Poll');
if (!defined('_ADVANCEDPOLLSCREATEFAILED')) {
    define('_ADVANCEDPOLLSCREATEFAILED', 'Creation attempt failed');
}
if (!defined('_ADVANCEDPOLLSDELETEFAILED')) {
    define('_ADVANCEDPOLLSDELETEFAILED', 'Deletion attempt failed');
}
if (!defined('_ADVANCEDPOLLSUPDATEFAILED')) {
    define('_ADVANCEDPOLLSUPDATEFAILED', 'Updating attempt failed');
}
define('_ADVANCEDPOLLSDELETE', 'Delete Poll');
define('_ADVANCEDPOLLSEDIT', 'Edit Poll');
define('_ADVANCEDPOLLSEDITCONFIG', 'Edit Polls Configuration');
define('_ADVANCEDPOLLSNEW', 'New Poll');
define('_ADVANCEDPOLLSADMIN', 'Advanced Polls Administration');
define('_ADVANCEDPOLLSADD', 'Create Poll');
define('_ADVANCEDPOLLSCREATED', 'Poll created');
define('_ADVANCEDPOLLSDELETED', 'Poll deleted');
define('_ADVANCEDPOLLSDISPLAYBOLD', 'Display item names in bold');
define('_ADVANCEDPOLLSNAME', 'Name of Poll');
define('_ADVANCEDPOLLSNOSUCHITEM', 'No such item');
define('_ADVANCEDPOLLSNUMBER', 'Poll ID');
define('_ADVANCEDPOLLSOPTIONS', 'Options');
define('_ADVANCEDPOLLSUPDATE', 'Update Poll');
define('_ADVANCEDPOLLSUPDATED', 'Poll updated');
define('_ADVANCEDPOLLSVIEW', 'View Polls');
define('_ADVANCEDPOLLSITEMSPERPAGE', 'Number of items per page');
define('_ADVANCEDPOLLSDATETIMESTART', 'Date and Time Poll Opens');
define('_ADVANCEDPOLLSDATETIMECLOSE', 'Date and Time Poll Closes');
define('_ADVANCEDPOLLSNEXT', 'Next');
define('_ADVANCEDPOLLSRECURRING','Recurring Poll?');
define('_ADVANCEDPOLLSRECURRINGOFFSET','Number of hours Poll reopens after');
if (!defined('_LANGUAGE')) {
    define('_LANGUAGE', 'Languages Poll is available in');
}
define('_ADVANCEDPOLLSVARIABLEERROR', 'Variable Error Will Robinson');
define('_ADVANCEDPOLLSAUTHTYPE', 'Poll Authorisation Method');
define('_ADVANCEDPOLLSMULTIPLESELECT', 'Selection Method');
define('_ADVANCEDPOLLSMULTIPLESELECTCOUNT', 'Number of Selections Allowed');
define('_ADVANCEDPOLLSMULTIPLESELECTCOUNTUNLIMITED', '(-1 for Unlimited Selections)');
define('_ADVANCEDPOLLSTIEBREAK', 'Tiebreak Method');
define('_ADVANCEDPOLLSRECURRINGINTERVAL', 'Number of Days Poll will recur after');
define('_ADVANCEDPOLLSONLYMULTIPLESELECT', 'Following option only relevant if a multiple selection poll is selected');
define('_ADVANCEDPOLLSONLYRECURRING', 'Following option only relevant if a recurring poll is selected');
define('_ADVANCEDPOLLSRESETVOTES', 'Reset Votes');
define('_ADVANCEDPOLLSADMINSTATS', 'Voting Statistics');
define('_ADVANCEDPOLLSVOTEID', 'Vote ID');
define('_ADVANCEDPOLLSVOTEIP', 'IP Address');
define('_ADVANCEDPOLLSVOTETIME', 'Time');
define('_ADVANCEDPOLLSVOTEUID', 'Username');
define('_ADVANCEDPOLLSVOTERANK', 'Vote Rank');
define('_ADVANCEDPOLLSVOTEOPTIONID', 'Option');
define('_ADVANCEDPOLLSADMINVOTINGSTATISTICS', 'Voting Statistics');
define('_ADVANCEDPOLLSADMINVOTINGSTATISTICSTABLE', 'Vote History');
define('_ADVANCEDPOLLSRESETVOTESCONFIRM', 'Reset Votes'); 
define('_ADVANCEDPOLLSCONFIRMVOTESRESET', 'Cofirm Reset of Votes'); 
define('_ADVANCEDPOLLSCANCELVOTESREST', 'Cancel Reset');
define('_ADVANCEDPOLLSVOTESRESET', 'Votes Reset');
define('_ADVANCEDPOLLSADMINDATEFORMAT', 'Date format for Admin Interface');
define('_ADVANCEDPOLLSUSERDATEFORMAT', 'Date format for User Interface');
define('_ADVANCEDPOLLSUSEREVERSEDNS', 'Use Reverse DNS for IP Addresses');
define('_ADVANCEDPOLLSSCALINGFACTOR', 'Scaling Factor for Poll Results Bar'); 
define('_ADVANCEDPOLLSDUPLICATE', 'Duplicate'); 
define('_ADVANCEDPOLLSCONFIRMDUPLICATE', 'Confirm Duplication of Poll');
define('_ADVANCEDPOLLSCANCELDUPLICATE', 'Cancel Duplication of Poll');
//define('_ADVANCEDPOLLSDUPLICATE', 'Duplicate Poll');
define('_ADVANCEDPOLLSSORTVOTESBY', 'Sort Votes by');
define('_ADVANCEDPOLLSSORTVOTESORDER', 'Sort Descending');
define('_ADVANCEDPOLLSSORTVOTES', 'Sort Votes');
define('_ADVANCEDPOLLSSORTASCENDING', 'Ascending');
define('_ADVANCEDPOLLSSORTDESCENDING', 'Descending');
define('_ADVANCEDPOLLSOPTION', 'Poll Option');
define('_ADVANCEDPOLLSNOAUTH', 'Not Authorised to Access Advanced Polls Module');
define('_ADVANCEDPOLLSDUPLICATED', 'Poll Duplicated');
define('_ADVANCEDPOLLSFREE', 'Free');
define('_ADVANCEDPOLLSUSERID', 'User ID');
define('_ADVANCEDPOLLSCOOKIE', 'Cookie');
define('_ADVANCEDPOLLSIPADDRESS', 'IP Address');
define('_ADVANCEDPOLLSNONE', 'None');
define('_ADVANCEDPOLLSVOTETIMECOUNTBACK', 'Vote Time Count Back');
define('_ADVANCEDPOLLSALPHABETICAL', 'Alphabetical');
define('_ADVANCEDPOLLSSINGLE', 'Single');
define('_ADVANCEDPOLLSMULTIPLE', 'Multiple');
define('_ADVANCEDPOLLSRANKED', 'Ranked');
define('_ADVANCEDPOLLSADMINITEMSPERPAGE', 'items per page in admin interface');
define('_ADVANCEDPOLLSUSERITEMSPERPAGE', 'items per page in user interface');
define('_ADVANCEDPOLLSDEFAULTCOLOUR', 'Default colour');
define('_ADVANCEDPOLLSDEFAULTOPTIONCOUNT', 'Default number of options in a poll');
define('_ADVANCEDPOLLSOPTIONCOUNT', 'Number of options in this poll');
define('_ADVANCEDPOLLNOSUCHITEM', 'No such poll');
define('_ADVANCEDPOLLSVOTECOUNT', 'Vote count');
define('_ADVANCEDPOLLSDESCRIPTION', 'Description');
define('_ADVANCEDPOLLSNOCLOSE', 'No close date');
define('_ADVANCEDPOLLSBASICINFO', 'Basic Information');
define('_ADVANCEDPOLLSTIMING', 'Timing');
define('_ADVANCEDPOLLSREGULATIONS', 'Voting regulations');

