<?php
class advanced_polls_contenttypesapi_pollPlugin extends contentTypeBase
{
    var $pollid;

    function getModule() { return 'advanced_polls'; }
    function getName() { return 'poll'; }
    function getTitle()
    {
        $dom = ZLanguage::getModuleDomain('advanced_polls');
        return __('Poll', $dom);
    }
    function getDescription()
    {
        $dom = ZLanguage::getModuleDomain('advanced_polls');
        return __('Display a single poll from the advanced polls module.', $dom);
    }
    function isTranslatable() { return false; }

    function loadData($data)
    {
        $this->pollid = $data['pollid'];
    }

    function display()
    {
        $poll = pnModFunc('advanced_polls', 'user', 'display', array('pollid' => (int) $this->pollid, 'displaytype' => 'short'));
        return $poll;
    }

    function displayEditing()
    {
        $dom = ZLanguage::getModuleDomain('advanced_polls');
        if (!empty($this->pollid))
        {
            return pnModFunc('advanced_polls', 'user', 'display', array('pollid' => (int) $this->pollid, 'displaytype' => 'short'));
        }
        return __('No poll selected', $dom);
    }

    function getDefaultData()
    {
        return array('pollid' => '');
    }


    function startEditing(&$render)
    {
        array_push($render->plugins_dir, 'modules/advanced_polls/pntemplates/pnform');
    }
}


function advanced_polls_contenttypesapi_poll($args)
{
    return new advanced_polls_contenttypesapi_pollPlugin();
}