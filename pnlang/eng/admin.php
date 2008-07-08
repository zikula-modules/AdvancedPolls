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

// main admin menu
define('_ADVANCEDPOLLSADMIN', 'Advanced Polls Administration');
define('_ADVANCEDPOLLSNEW', 'New Poll');
define('_ADVANCEDPOLLSVIEW', 'View Polls');

// delete template
define('_ADVANCEDPOLLSCONFIRMDELETE', 'Confirm deletion of Poll');
define('_ADVANCEDPOLLSDELETE', 'Delete Poll');

// create/edit templates
define('_ADVANCEDPOLLSADD', 'Create Poll');
define('_ADVANCEDPOLLSAUTHTYPE', 'Poll Authorisation Method');
define('_ADVANCEDPOLLSBASICINFO', 'Basic Information');
define('_ADVANCEDPOLLSDATETIMESTART', 'Date and Time Poll Opens');
define('_ADVANCEDPOLLSDATETIMECLOSE', 'Date and Time Poll Closes');
define('_ADVANCEDPOLLSDESCRIPTION', 'Description');
define('_ADVANCEDPOLLSEDIT', 'Edit Poll');
define('_ADVANCEDPOLLSMULTIPLESELECT', 'Selection Method');
define('_ADVANCEDPOLLSMULTIPLESELECTCOUNT', 'Number of Selections Allowed');
define('_ADVANCEDPOLLSMULTIPLESELECTCOUNTUNLIMITED', '(-1 for Unlimited Selections)');
define('_ADVANCEDPOLLSNAME', 'Name of Poll');
define('_ADVANCEDPOLLSNOCLOSE', 'No close date');
define('_ADVANCEDPOLLSONLYMULTIPLESELECT', 'Following option only relevant if a multiple selection poll is selected');
define('_ADVANCEDPOLLSONLYRECURRING', 'Following option only relevant if a recurring poll is selected');
define('_ADVANCEDPOLLSOPTION', 'Poll Option');
define('_ADVANCEDPOLLSOPTIONCOUNT', 'Number of options in this poll');
define('_ADVANCEDPOLLSOPTIONS', 'Options');
define('_ADVANCEDPOLLSRECURRING','Recurring Poll?');
define('_ADVANCEDPOLLSRECURRINGINTERVAL', 'Number of Days Poll will recur after');
define('_ADVANCEDPOLLSRECURRINGOFFSET','Number of hours Poll reopens after');
define('_ADVANCEDPOLLSREGULATIONS', 'Voting regulations');
define('_ADVANCEDPOLLSTIEBREAK', 'Tiebreak Method');
define('_ADVANCEDPOLLSTIMING', 'Timing');

// view template
define('_ADVANCEDPOLLSADMINSTATS', 'Voting Statistics');
define('_ADVANCEDPOLLSDUPLICATE', 'Duplicate Poll');
define('_ADVANCEDPOLLSISOPEN', 'Is poll open?');
define('_ADVANCEDPOLLSNUMBER', 'Poll ID');
define('_ADVANCEDPOLLSRESETVOTES', 'Reset Votes');

// modify config template
define('_ADVANCEDPOLLSADMINITEMSPERPAGE', 'items per page in admin interface');
define('_ADVANCEDPOLLSADMINDATEFORMAT', 'Date format for Admin Interface');
define('_ADVANCEDPOLLSUSERDATEFORMAT', 'Date format for User Interface');
define('_ADVANCEDPOLLSDEFAULTCOLOUR', 'Default colour');
define('_ADVANCEDPOLLSDEFAULTOPTIONCOUNT', 'Default number of options in a poll');
define('_ADVANCEDPOLLSDISPLAYBOLD', 'Display item names in bold');
define('_ADVANCEDPOLLSSCALINGFACTOR', 'Scaling Factor for Poll Results Bar'); 
define('_ADVANCEDPOLLSUSEREVERSEDNS', 'Use Reverse DNS for IP Addresses');
define('_ADVANCEDPOLLSUSERITEMSPERPAGE', 'items per page in user interface');

// reset votes template
define('_ADVANCEDPOLLSCONFIRMVOTESRESET', 'Cofirm Reset of Votes'); 
define('_ADVANCEDPOLLSRESETVOTESCONFIRM', 'Reset Votes'); 

// duplicate poll template
define('_ADVANCEDPOLLSCONFIRMDUPLICATE', 'Confirm Duplication of Poll');
define('_ADVANCEDPOLLSCANCELDUPLICATE', 'Cancel Duplication of Poll');

// voting statistics template
define('_ADVANCEDPOLLSVOTECOUNT', 'Vote count');
define('_ADVANCEDPOLLSSORTVOTES', 'Sort Votes');
define('_ADVANCEDPOLLSSORTVOTESBY', 'Sort Votes by');
define('_ADVANCEDPOLLSSORTVOTESORDER', 'Sort Order');
define('_ADVANCEDPOLLSVOTEID', 'Vote ID');
define('_ADVANCEDPOLLSVOTEIP', 'IP Address');
define('_ADVANCEDPOLLSVOTEOPTIONID', 'Option');
define('_ADVANCEDPOLLSVOTERANK', 'Vote Rank');
define('_ADVANCEDPOLLSVOTETIME', 'Time');
define('_ADVANCEDPOLLSVOTEUID', 'Username');
define('_ADVANCEDPOLLSADMINVOTINGSTATISTICS', 'Voting Statistics');
define('_ADVANCEDPOLLSADMINVOTINGSTATISTICSTABLE', 'Vote History');

// voting regulations drop down
define('_ADVANCEDPOLLSCOOKIE', 'Cookie');
define('_ADVANCEDPOLLSFREE', 'Free');
define('_ADVANCEDPOLLSIPADDRESS', 'IP Address');
define('_ADVANCEDPOLLSUSERID', 'User ID');

// tiebreak drop down
define('_ADVANCEDPOLLSALPHABETICAL', 'Alphabetical');
define('_ADVANCEDPOLLSNONE', 'None');
define('_ADVANCEDPOLLSVOTETIMECOUNTBACK', 'Vote Time Count Back');

// multiple select drop down
define('_ADVANCEDPOLLSSINGLE', 'Single');
define('_ADVANCEDPOLLSMULTIPLE', 'Multiple');
define('_ADVANCEDPOLLSRANKED', 'Ranked');

// sort order drop down
define('_ADVANCEDPOLLSSORTASCENDING', 'Ascending');
define('_ADVANCEDPOLLSSORTDESCENDING', 'Descending');

// error status messages
define('_ADVANCEDPOLLSCREATED', 'Poll created');
define('_ADVANCEDPOLLSDELETED', 'Poll deleted');
define('_ADVANCEDPOLLSDUPLICATED', 'Poll Duplicated');
define('_ADVANCEDPOLLSUPDATED', 'Poll updated');
define('_ADVANCEDPOLLSVOTESRESETFAILED', 'Vote reset failed');
