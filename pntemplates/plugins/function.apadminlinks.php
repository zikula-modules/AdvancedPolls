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
 * Smarty function to display admin links for the advanced polls module
 * based on the user's permissions
 * 
 * Example
 * <!--[apadminlinks start="[" end="]" seperator="|" class="pn-menuitem-title"]-->
 * 
 * @author       Mark West
 * @since        21/01/04
 * @see          function.exampleadminlinks.php::smarty_function_exampleadminlinks()
 * @param        array       $params      All attributes passed to this function from the template
 * @param        object      &$smarty     Reference to the Smarty object
 * @param        string      $start       start string
 * @param        string      $end         end string
 * @param        string      $seperator   link seperator
 * @param        string      $class       CSS class
 * @return       string      the results of the module function
 */
function smarty_function_apadminlinks($params, &$smarty) 
{
    extract($params); 
	unset($params);
    
	// set some defaults
	// set some defaults
	if (!isset($start)) {
		$start = '[';
	}
	if (!isset($end)) {
		$end = ']';
	}
	if (!isset($seperator)) {
		$seperator = '|';
	}
    if (!isset($class)) {
	    $class = 'pn-menuitem-title';
	}

    $adminlinks = "<span class=\"$class\">$start ";
	
    if (SecurityUtil::checkPermission('advanced_polls::', "::", ACCESS_READ)) {
		$adminlinks .= "<a href=\"" . pnVarPrepHTMLDisplay(pnModURL('advanced_polls', 'admin', 'view')) . "\">" . _ADVANCEDPOLLSVIEW . "</a> ";
    }
    if (SecurityUtil::checkPermission('advanced_polls::', "::", ACCESS_ADD)) {
		$adminlinks .= "$seperator <a href=\"" . pnVarPrepHTMLDisplay(pnModURL('advanced_polls', 'admin', 'new')) . "\">" . _ADVANCEDPOLLSNEW . "</a> ";
    }
    if (SecurityUtil::checkPermission('advanced_polls::', "::", ACCESS_ADMIN)) {
		$adminlinks .= "$seperator <a href=\"" . pnVarPrepHTMLDisplay(pnModURL('advanced_polls', 'admin', 'modifyconfig')) . "\">" . _ADVANCEDPOLLSMODIFYCONFIG . "</a> ";
    }

	$adminlinks .= "$end</span>\n";

    return $adminlinks;
}

