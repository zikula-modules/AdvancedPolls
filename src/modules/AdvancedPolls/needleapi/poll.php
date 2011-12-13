<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * AdvancedPolls needle
 * @param $args['nid'] needle id
 * @return array()
 */
function AdvancedPolls_needleapi_poll($args)
{
    // Get arguments from argument array
    $nid = $args['nid'];
    unset($args);

    // cache the results
    static $cache;

    if (!isset($cache)) {
        $cache = array();
    }

    $dom = ZLanguage::getModuleDomain('AdvancedPolls');

    if (!empty($nid)) {
        if (!isset($cache[$nid])) {
            // not in cache array

            $obj = ModUtil::apiFunc('AdvancedPolls', 'user', 'get', array('pollid' => $nid));

            if ($obj != false) {
                $url   = DataUtil::formatForDisplay(ModUtil::url('AdvancedPolls', 'user', 'display', array('pollid' => $nid)));
                $title = DataUtil::formatForDisplay($obj['title']);
                $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
            } else {
                $cache[$nid] = '<em>' . __f("Error! Poll ID '%s' not found.", $nid, $dom) . '</em>';
            }
        }
        $result = $cache[$nid];
    } else {
        $result = '<em>' . __('Error! No needle ID provided.', $dom) . '</em>';
    }

    return $result;
}
