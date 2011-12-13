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
	
	public function getMetaData() {	

		$meta['displayname']     = $this->__('Advanced Polls');
		$meta['description']     = $this->__('A comprehensive single question polling module');
		$meta['url']             = $this->__('advancedpolls');
		$meta['oldnames']        = array('advanced_polls');
		$meta['core_min']        = '1.3.0'; // requires minimum 1.3.0 or later
		$meta['capabilities']    = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
		$meta['contact']         = 'Advanced Polls Development Team, Michael Ueberschaer';
		$meta['securityschema']  = array('AdvancedPolls::item' => 'Poll Title::Poll ID',
                                       'AdvancedPolls::' => '::');
		$meta['dependencies']    = array(array('modname'    => 'EZComments',
                                             	'minversion' => '3.0.0',
                                             	'maxversion' => '',
                                             	'status'     => PNMODULE_DEPENDENCY_RECOMMENDED));

		return $meta;
}
}