<?php

/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */


class AdvancedPolls_Handler_ModifyConfig extends Zikula_Form_AbstractHandler
{
    /**
     * poll.
     *
     * When set this handler is in edit mode.
     *
     * @var integer
     */
    private $poll;

    

    function initialize(Zikula_Form_View $view)
    {
        

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        $this->view->assign($this->getVars());
        
        return true;
    }

    

    function handleCommand(Zikula_Form_View $view, &$args)
    {
        
        $url = ModUtil::url($this->name, 'admin', 'modifyconfig');
        if ($args['commandName'] == 'cancel') { 
            return $view->redirect($url);
        }
        
         
        
        
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }   

        // load form values
        $config = $view->getValues();
                print_r($config);

        $this->setVars($config);
        
        // the module configuration has been updated successfuly
        LogUtil::registerStatus ($this->__('Done! Module configuration updated.'));
        
        
        return $view->redirect($url);;
    }

}