<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

function smarty_function_sortordertypes($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    extract($params);

    unset($params['name']);
    unset($params['id']);
    unset($params['selected']);
    unset($params['type']);

    $options = array('0' => __('Ascending', $dom),
                     '1' => __('Descending', $dom));

    // we'll make use of the html_options plugin to simplfiy this plugin
    require_once $smarty->_get_plugin_filepath('function','html_options');

    // get the formatted list
    $output = smarty_function_html_options(array('options'   => $options,
                                                 'selected'  => isset($selected) ? $selected : null,
                                                 'name'      => isset($name) ? $name : null,
                                                 'id'        => isset($id) ? $id : null),
                                                 $smarty);

    if (isset($assign)) {
        $smarty->assign($assign, $output);
    } else {
        return $output;
    }
}
