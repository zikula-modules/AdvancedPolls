<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West, Carsten Volmer
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Advanced_polls needle
 * @param $args['nid'] needle id
 * @return array()
 */
function advanced_polls_needleapi_poll($args)
{
    // Get arguments from argument array
    $nid = $args['nid'];
    unset($args);

    // cache the results
    static $cache;

    if (!isset($cache)) {
        $cache = array();
    }

    $dom = ZLanguage::getModuleDomain('advanced_polls');

    if (!empty($nid)) {
        if (!isset($cache[$nid])) {
            // not in cache array

            $obj = pnModAPIFunc('advanced_polls', 'user', 'get', array('pollid' => $nid));

            if ($obj != false) {
                $url   = DataUtil::formatForDisplay(pnModURL('advanced_polls', 'user', 'display', array('pollid' => $nid)));
                $title = DataUtil::formatForDisplay($obj['title']);
                $cache[$nid] = '<a href="' . $url . '" title="' . $title . '">' . $title . '</a>';
            } else {
                $cache[$nid] = '<em>' . __f("Error! Database contains no poll with the ID '%s'.", $nid, $dom) . '</em>';
            }
        }
        $result = $cache[$nid];
    } else {
        $result = '<em>' . __('Error! No needle ID provided.', $dom) . '</em>';
    }

    return $result;
}
