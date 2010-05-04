<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2010 by Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
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

