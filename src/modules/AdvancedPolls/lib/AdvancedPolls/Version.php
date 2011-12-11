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

		$dom = ZLanguage::getModuleDomain('advanced_polls');
		$meta['name']            = 'advanced_polls';
		$meta['displayname']     = __('Advanced Polls', $dom);
		$meta['description']     = __('A comprehensive single question polling module', $dom);
		$meta['url']             = __('advancedpolls', $dom);
		$meta['version']         = '2.0.1';
		$meta['credits']         = 'docs/credits.txt';
		$meta['help']            = 'docs/help.txt';
		$meta['changelog']       = 'docs/changelog.txt';
		$meta['license']         = 'docs/license.txt';
		$meta['official']        = 0;
		$meta['author']          = 'Mark West, Carsten Volmer, Michael Ueberschaer';
		$meta['contact']         = 'http://code.zikula.org/advancedpolls/';
		$meta['admin']           = 1;
		$meta['securityschema']  = array('advanced_polls::item' => 'Poll Title::Poll ID',
                                       'advanced_polls::' => '::');
		$meta['dependencies']    = array(array('modname'    => 'EZComments',
                                             	'minversion' => '3.0.0',
                                             	'maxversion' => '',
                                             	'status'     => PNMODULE_DEPENDENCY_RECOMMENDED));

		return $meta;
}
}