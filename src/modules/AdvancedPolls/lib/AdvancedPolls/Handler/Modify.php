<?php

/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */


class AdvancedPolls_Handler_Modify extends Zikula_Form_AbstractHandler
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
        

         $pollid = FormUtil::getPassedValue('pollid', null, "GET", FILTER_SANITIZE_NUMBER_INT);

         $modvars = $this->getVars();
         $this->view->assign($modvars);

         
         if ($pollid) {
             // Security check.
            if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_EDIT)) {
                return LogUtil::registerPermissionError();
            }    
             
            $view->assign('templatetitle', $this->__('Edit poll'));
            
            $this->poll = $this->entityManager->getRepository('AdvancedPolls_Entity_Desc')
                                 ->find($pollid);
            

            if ($this->poll) {
                $view->assign($this->poll->getAll());
            } else {
                return LogUtil::registerError($this->__f('Poll with tid %s not found', $pollid));
            }
            
            
            // get vote counts
            $votecount = ModUtil::apiFunc($this->name, 'user', 'pollvotecount', array('pollid' => $pollid));

            if ($modvars['enablecategorization']) {
                // load the category registry util
                if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
                    pn_exit ($this->__f('Error! Unable to load class [%s]', array('s' => 'CategoryRegistryUtil')));
                }
                $catregistry = CategoryRegistryUtil::getRegisteredModuleCategories($this->name, 'advanced_polls_desc');

                $this->view->assign('catregistry', $catregistry);
            }

            $poll = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid, 'parse' => true));
            $this->view->assign($poll);
        } else {
            // new poll
            
            // Security check
            if (!SecurityUtil::checkPermission('AdvancedPolls::item', '::', ACCESS_ADD)) {
                return LogUtil::registerPermissionError();
            }

            $view->assign('templatetitle', $this->__('New poll'));
            $view->assign('optioncount', $this->getVar('defaultoptioncount', 10));
            
        }
        
        
        $this->view->assign('dateformat', $this->__('%Y-%m-%d %H:%M:%S') );
        
        

        
        return true;
    }

    

    function handleCommand(Zikula_Form_View $view, &$args)
    {
        // switch between edit and create mode        
        /*if ($this->poll) {        
            $url = ModUtil::url('Tasks', 'user', 'view', array(
                'tid' => $this->_tid
            ) );
        } else {
            $url = ModUtil::url('Tasks', 'user', 'main');
        }*/
        

        $url = ModUtil::url($this->name, 'admin', 'view');
        
        if ($args['commandName'] == 'cancel') { 
            return $view->redirect($url);
        }
        
        
       
        
        
        // check for valid form
        if (!$view->isValid()) {
            return false;
        }
        


        // load form values
        $data = $view->getValues();
        
        

        
        $options =array();
        $texts  = $data['option_texts'];  
        $colors = $data['option_colors'];
        while ($t = current($texts)){
            $options[] = array(
                'optiontext'   => $t,
                'optioncolour' => current($colors),
                'pollid'       => 1
            );
            next($texts);
            next($colors);
        }
        
        $data['options'] = $options;
        unset($data['option_texts']);
        unset($data['option_colors']);
        
        
                
        
        // switch between edit and create mode        
        if ($this->poll) {
            $poll = $this->poll;
        } else {
            $poll = new AdvancedPolls_Entity_Desc();
        }
        
    
        
        
        //$poll->set($data['title'], 'title');
        $poll->setAll($data);
        $this->entityManager->persist($poll);
        $this->entityManager->flush();
        
        
        return $this->view->redirect($url);
    }

}