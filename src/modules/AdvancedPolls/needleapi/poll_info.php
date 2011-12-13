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
 * Advanced_polls needle info
 * @param none
 * @return string with short usage description
 */
function advanced_polls_needleapi_poll_info()
{
    $info = array('module'  => 'advanced_polls', // module name
                  'info'    => 'POLL{id}', // possible needles
                  'inspect' => false); //reverse lookpup possible, needs MultiHook_needleapi_content_inspect() function
    return $info;
}
