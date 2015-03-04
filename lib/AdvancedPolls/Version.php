<?php

/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * 
 */

class AdvancedPolls_Version extends Zikula_AbstractVersion {
	
    public function getMetaData()
    {
        $meta = array();
        $meta['displayname']     = $this->__('Advanced Polls');
        $meta['description']     = $this->__('A comprehensive single question polling module');
        $meta['url']             = $this->__('advancedpolls');
        $meta['version']          = '3.0.0';
        $meta['oldnames']        = array('advanced_polls');
        $meta['core_min']        = '1.3.0'; // requires minimum 1.3.0 or later
        $meta['capabilities']    = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true),
                                        HookUtil::PROVIDER_CAPABLE => array('enabled' => true));
        $meta['contact']         = 'Advanced Polls Development Team, Michael Ueberschaer';
        $meta['securityschema']  = array('AdvancedPolls::item' => 'Poll Title::Poll ID',
                                'AdvancedPolls::' => '::');
        $meta['dependencies']    = array(array('modname'    => 'EZComments',
                                        'minversion' => '3.0.0',
                                        'maxversion' => '',
                                        'status'     => ModUtil::DEPENDENCY_RECOMMENDED));
        return $meta;
    }

    protected function setupHookBundles()
    {
        // Register subscriber hooks
        $bundle = new Zikula_HookManager_SubscriberBundle($this->name, 'subscriber.advancedpolls.ui_hooks.advancedpolls', 'ui_hooks', $this->__('Polls Hooks'));
        $bundle->addEvent('display_view', 'advancedpolls.ui_hooks.polls.display_view');
        $bundle->addEvent('form_edit', 'advancedpolls.ui_hooks.polls.form_edit');
        $this->registerHookSubscriberBundle($bundle);
        // Register provider hooks
        $bundle = new Zikula_HookManager_ProviderBundle($this->name, 'provider.advancedpolls.ui_hooks.poll', 'ui_hooks', $this->__('Poll Item'));
        $bundle->addServiceHandler('display_view', 'AdvancedPolls_HookHandlers', 'uiView', 'advancedpolls.poll');
        $this->registerHookProviderBundle($bundle);
    }
}
