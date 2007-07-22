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

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 *
 * @since 1.0
*/
function advanced_polls_pntables() 
{
	$pntable = array();

	// Votes table
	$pntable['advanced_polls_votes'] = DBUtil::getLimitedTablename('advanced_polls_votes');
	$pntable['advanced_polls_votes_column'] = array('pn_ip'       => 'pn_ip',
											        'pn_time'     => 'pn_time',
                                                    'pn_uid'      => 'pn_uid',
                                                    'pn_voterank' => 'pn_voterank',
                                                    'pn_pollid'   => 'pn_pollid',
                                                    'pn_optionid' => 'pn_optionid',
                                                    'pn_voteid'   => 'pn_voteid');
    $pntable['advanced_polls_votes_column_def'] = array('pn_voteid'   => 'I AUTOINCREMENT PRIMARY',
                                                        'pn_ip'       => "C(20) NOTNULL DEFAULT ''",
                                                        'pn_time'     => "C(14) NOTNULL DEFAULT ''",
                                                        'pn_uid'      => "I NOTNULL DEFAULT '0'",
                                                        'pn_voterank' => "I NOTNULL DEFAULT '0'",
                                                        'pn_pollid'   => "I NOTNULL DEFAULT '0'",
                                                        'pn_optionid' => "I NOTNULL DEFAULT '0'");

	// Poll options data
	$pntable['advanced_polls_data'] = DBUtil::getLimitedTablename('advanced_polls_data');
	$pntable['advanced_polls_data_column'] = array('pn_pollid'       => 'pn_pollid',
                                                   'pn_optiontext'   => 'pn_optiontext',
                                                   'pn_optionid'     => 'pn_optionid',
                                                   'pn_optioncolour' => 'pn_optioncolour');
    $pntable['advanced_polls_data_column_def'] = array('pn_pollid'       => "I NOTNULL DEFAULT '0'",
                                                       'pn_optiontext'   => "C(255) NOTNULL DEFAULT ''",
                                                       'pn_optionid'     => "I NOTNULL DEFAULT '0'",
                                                       'pn_optioncolour' => "C(7) NOTNULL DEFAULT ''");

	// Poll descriptions
	$pntable['advanced_polls_desc'] = DBUtil::getLimitedTablename('advanced_polls_desc');
	$pntable['advanced_polls_desc_column'] = array('pn_pollid'              => 'pn_pollid',
                                                   'pn_title'               => 'pn_title',
                                                   'pn_description'         => 'pn_description',
                                                   'pn_optioncount'         => 'pn_optioncount',
                                                   'pn_opendate'            => 'pn_opendate',
                                                   'pn_closedate'           => 'pn_closedate',
                                                   'pn_recurring'           => 'pn_recurring',
                                                   'pn_recurringoffset'     => 'pn_recurringoffset',
                                                   'pn_recurringinterval'   => 'pn_recurringinterval',
                                                   'pn_multipleselect'      => 'pn_multipleselect',
                                                   'pn_multipleselectcount' => 'pn_multipleselectcount',
                                                   'pn_voteauthtype'        => 'pn_voteauthtype',
                                                   'pn_tiebreakalg'         => 'pn_tiebreakalg',
                                                   'pn_language'            => 'pn_language',
                                                   'pn_votingmethod'        => 'pn_votingmethod');
    $pntable['advanced_polls_desc_column_def'] = array('pn_pollid'              => 'I AUTOINCREMENT PRIMARY',
                                                       'pn_title'               => "C(100) NOTNULL DEFAULT ''",
                                                       'pn_description'         => 'X2',
                                                       'pn_optioncount'         => "I NOTNULL DEFAULT '0'",
                                                       'pn_opendate'            => "I NOTNULL DEFAULT '0'",
                                                       'pn_closedate'           => "I NOTNULL DEFAULT '0'",
                                                       'pn_recurring'           => "I NOTNULL DEFAULT '0'",
                                                       'pn_recurringoffset'     => "I NOTNULL DEFAULT '0'",
                                                       'pn_recurringinterval'   => "I NOTNULL DEFAULT '0'",
                                                       'pn_multipleselect'      => "I NOTNULL DEFAULT '0'",
                                                       'pn_multipleselectcount' => "I NOTNULL DEFAULT '0'",
                                                       'pn_voteauthtype'        => "I NOTNULL DEFAULT '0'",
                                                       'pn_tiebreakalg'         => "I NOTNULL DEFAULT '0'",
                                                       'pn_language'            => "C(30) NOTNULL DEFAULT ''",
                                                       'pn_votingmethod'        => "I NOTNULL DEFAULT '0'");

	// Return the table information
	return $pntable;
}

