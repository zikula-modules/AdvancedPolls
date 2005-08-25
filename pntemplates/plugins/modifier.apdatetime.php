<?php
// $Id$
// ----------------------------------------------------------------------
// Advanced Polls Module for the POST-NUKE Content Management System
// Copyright (C) 2002-2004 by Mark West
// http://www.markwest.me.uk/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
/**
 * Adnvaced Polls plugin
 * 
 * This file is a plugin for pnRender, the PostNuke implementation of Smarty
 *
* @package Advanced_Polls
 * @version      $Id$
 * @author       Mark West
 * @link         http://www.markwest.me.uk
 * @copyright (C) 2003, 2004 by Mark West
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
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

?>