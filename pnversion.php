<?php
/**
 * Advanced Polls module for PostNuke
 *
 * @author Mark West <mark@markwest.me.uk> 
 * @copyright (C) 2002-2007 by Mark West
 * @link http://www.markwest.me.uk Advanced Polls Support Site
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package PostNuke_3rdParty_Modules
 * @subpackage Advanced_Polls
 */

$modversion['name'] = 'advanced_polls';
$modversion['displayname'] = 'Advanced Polls';
$modversion['description'] = 'Advanced Polls Module';
$modversion['version'] = '1.51';
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
