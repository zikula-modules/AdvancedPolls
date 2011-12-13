<?php



/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West, Carsten Volmer
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * 
 */

class AdvancedPolls_Version extends Zikula_AbstractVersion {
	
	public function getMetaData() {	

		$meta['name']            = 'advanced_polls';
		$meta['displayname']     = $this->__('Advanced Polls');
		$meta['description']     = $this->__('A comprehensive single question polling module');
		$meta['url']             = $this->__('advancedpolls');
		$meta['oldnames']        = array('advanced_polls');
		$meta['version']         = '2.0.1';
		$meta['core_min']        = '1.3.0'; // requires minimum 1.3.0 or later
		$meta['capabilities']    = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));
		$meta['contact']         = 'Mark West, Carsten Volmer, Michael Ueberschaer';
		$meta['securityschema']  = array('advanced_polls::item' => 'Poll Title::Poll ID',
                                       'advanced_polls::' => '::');
		$meta['dependencies']    = array(array('modname'    => 'EZComments',
                                             	'minversion' => '3.0.0',
                                             	'maxversion' => '',
                                             	'status'     => PNMODULE_DEPENDENCY_RECOMMENDED));

		return $meta;
}
}