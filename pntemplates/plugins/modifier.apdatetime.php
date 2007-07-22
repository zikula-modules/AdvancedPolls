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

require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
function smarty_modifier_apdatetime($string, $type='user')
{
	if($string != '') {
    	return ml_ftime(constant(pnModGetVar('advanced_polls', "{$type}dateformat")), smarty_make_timestamp($string));
	} else {
	    return;
	}
}

