<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2010 by Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

$dom = ZLanguage::getModuleDomain('advanced_polls');
$modversion['name']            = 'advanced_polls';
$modversion['displayname']     = __('Advanced Polls', $dom);
$modversion['description']     = __('A comprehensive single question polling module', $dom);
$modversion['url']             = __('advanced_polls', $dom);
$modversion['version']         = '2.0';
$modversion['credits']         = 'pndocs/credits.txt';
$modversion['help']            = 'pndocs/help.txt';
$modversion['changelog']       = 'pndocs/changelog.txt';
$modversion['license']         = 'pndocs/license.txt';
$modversion['official']        = 0;
$modversion['author']          = 'Mark West';
$modversion['contact']         = 'http://www.markwest.me.uk/';
$modversion['admin']           = 1;
$modversion['securityschema']  = array('advanced_polls::item' => 'Poll Title::Poll ID',
                                      'advanced_polls::' => '::');
$modversion['dependencies']    = array(array('modname'    => 'EZComments', 
                                             'minversion' => '1.1',
                                             'maxversion' => '',
                                             'status'     => PNMODULE_DEPENDENCY_RECOMMENDED));
