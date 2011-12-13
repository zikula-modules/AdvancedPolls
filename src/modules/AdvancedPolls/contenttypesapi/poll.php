<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class AdvancedPolls_contenttypesapi_pollPlugin extends contentTypeBase
{
    var $pollid;

    function getModule() { return 'AdvancedPolls'; }
    function getName() { return 'poll'; }
    function getTitle()
    {
        $dom = ZLanguage::getModuleDomain('AdvancedPolls');
        return __('Poll', $dom);
    }
    function getDescription()
    {
        $dom = ZLanguage::getModuleDomain('AdvancedPolls');
        return __('Display a single poll from the advanced polls module.', $dom);
    }
    function isTranslatable() { return false; }

    function loadData($data)
    {
        $this->pollid = $data['pollid'];
    }

    function display()
    {
        $poll = ModUtil::func('AdvancedPolls', 'user', 'display', array('pollid' => (int) $this->pollid, 'displaytype' => 'short'));
        return $poll;
    }

    function displayEditing()
    {
        $dom = ZLanguage::getModuleDomain('AdvancedPolls');
        if (!empty($this->pollid))
        {
            return ModUtil::func('AdvancedPolls', 'user', 'display', array('pollid' => (int) $this->pollid, 'displaytype' => 'short'));
        }
        return __('No poll selected', $dom);
    }

    function getDefaultData()
    {
        return array('pollid' => '');
    }


    function startEditing(&$render)
    {
        array_push($render->plugins_dir, 'modules/AdvancedPolls/pntemplates/pnform');
    }
}


function AdvancedPolls_contenttypesapi_poll($args)
{
    return new AdvancedPolls_contenttypesapi_pollPlugin();
}