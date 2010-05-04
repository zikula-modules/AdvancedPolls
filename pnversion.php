<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Zikula_3rdParty_Modules
 * @subpackage Advanced_Polls
 */

$modversion['name'] = _ADVANCEDPOLLS__('Name', $dom);
$modversion['displayname'] = _ADVANCEDPOLLS_DISPLAYNAME;
$modversion['description'] = _ADVANCEDPOLLS__('Description', $dom);
$modversion['version'] = '2.0';
$modversion['credits'] = 'pndocs/credits.txt';
$modversion['help'] = 'pndocs/help.txt';
$modversion['changelog'] = 'pndocs/changelog.txt';
$modversion['license'] = 'pndocs/license.txt';
$modversion['official'] = 0;
$modversion['author'] = 'Mark West';
$modversion['contact'] = 'http://www.markwest.me.uk/';
$modversion['admin'] = 1;
$modversion['securityschema'] = array('advanced_polls::item' => 'Poll Title::Poll ID',
                                      'advanced_polls::' => '::');
$modversion['dependencies']    = array(array('modname'    => 'EZComments', 
                                             'minversion' => '1.1',
                                             'maxversion' => '',
                                             'status'     => PNMODULE_DEPENDENCY_RECOMMENDED));
