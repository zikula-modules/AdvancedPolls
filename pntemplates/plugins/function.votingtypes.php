<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id: modifier.apdatetime.php 66 2008-06-19 12:20:13Z markwest $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Advanced_Polls
 */

function smarty_function_votingtypes($params, &$smarty)
{
    extract($params);

    unset($params['name']);
    unset($params['id']);
    unset($params['selected']);
    unset($params['type']);

    $options = array('1' => _ADVANCEDPOLLSFREE, '2' => _ADVANCEDPOLLSUSERID,
					 '3' => _ADVANCEDPOLLSCOOKIE, '4' => _ADVANCEDPOLLSIPADDRESS);

    // we'll make use of the html_options plugin to simplfiy this plugin
    require_once $smarty->_get_plugin_filepath('function','html_options');

    // get the formatted list
    $output = smarty_function_html_options(array('options'   => $options,
                                                 'selected'  => isset($selected) ? $selected : null,
                                                 'name'      => $name,
                                                 'id'        => isset($id) ? $id : null),
                                                 $smarty);

	if (isset($assign)) {
		$smarty->assign($assign, $output);
	} else {
		return $output;
	}
}
