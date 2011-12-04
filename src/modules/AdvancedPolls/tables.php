<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West, Carsten Volmer
 * @copyright (C) 2002-2010 by Advanced Polls Development Team
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * This function is called internally by the core whenever the module is
 * loaded.  It adds in the information
 */
function advancedpolls_tables()
{
    $pntable = array();

    // Votes table
    $pntable['advanced_polls_votes'] = DBUtil::getLimitedTablename('advanced_polls_votes');
    $pntable['advanced_polls_votes_column'] = array('ip'       => 'pn_ip',
                                                    'time'     => 'pn_time',
                                                    'uid'      => 'pn_uid',
                                                    'voterank' => 'pn_voterank',
                                                    'pollid'   => 'pn_pollid',
                                                    'optionid' => 'pn_optionid',
                                                    'voteid'   => 'pn_voteid');
    $pntable['advanced_polls_votes_column_def'] = array('voteid'   => 'I AUTOINCREMENT PRIMARY',
                                                        'ip'       => "C(20) NOTNULL DEFAULT ''",
                                                        'time'     => "C(14) NOTNULL DEFAULT ''",
                                                        'uid'      => "I NOTNULL DEFAULT '0'",
                                                        'voterank' => "I NOTNULL DEFAULT '0'",
                                                        'pollid'   => "I NOTNULL DEFAULT '0'",
                                                        'optionid' => "I NOTNULL DEFAULT '0'");

    // Poll options data
    $pntable['advanced_polls_data'] = DBUtil::getLimitedTablename('advanced_polls_data');
    $pntable['advanced_polls_data_column'] = array('pollid'       => 'pn_pollid',
                                                   'optiontext'   => 'pn_optiontext',
                                                   'optionid'     => 'pn_optionid',
                                                   'optioncolour' => 'pn_optioncolour');
    $pntable['advanced_polls_data_column_def'] = array('pollid'       => "I NOTNULL DEFAULT '0'",
                                                       'optiontext'   => "C(255) NOTNULL DEFAULT ''",
                                                       'optionid'     => "I NOTNULL DEFAULT '0'",
                                                       'optioncolour' => "C(7) NOTNULL DEFAULT ''");

    // Poll descriptions
    $pntable['advanced_polls_desc'] = DBUtil::getLimitedTablename('advanced_polls_desc');
    $pntable['advanced_polls_desc_column'] = array('pollid'              => 'pn_pollid',
                                                   'title'               => 'pn_title',
                                                   'urltitle'            => 'pn_urltitle',
                                                   'description'         => 'pn_description',
                                                   'optioncount'         => 'pn_optioncount',
                                                   'opendate'            => 'pn_opendate',
                                                   'closedate'           => 'pn_closedate',
                                                   'recurring'           => 'pn_recurring',
                                                   'recurringoffset'     => 'pn_recurringoffset',
                                                   'recurringinterval'   => 'pn_recurringinterval',
                                                   'multipleselect'      => 'pn_multipleselect',
                                                   'multipleselectcount' => 'pn_multipleselectcount',
                                                   'voteauthtype'        => 'pn_voteauthtype',
                                                   'tiebreakalg'         => 'pn_tiebreakalg',
                                                   'language'            => 'pn_language');
    $pntable['advanced_polls_desc_column_def'] = array('pollid'              => 'I AUTOINCREMENT PRIMARY',
                                                       'title'               => "C(100) NOTNULL DEFAULT ''",
                                                       'urltitle'            => "C(120) NOTNULL DEFAULT ''",
                                                       'description'         => 'X2',
                                                       'optioncount'         => "I NOTNULL DEFAULT '0'",
                                                       'opendate'            => "I NOTNULL DEFAULT '0'",
                                                       'closedate'           => "I NOTNULL DEFAULT '0'",
                                                       'recurring'           => "I NOTNULL DEFAULT '0'",
                                                       'recurringoffset'     => "I NOTNULL DEFAULT '0'",
                                                       'recurringinterval'   => "I NOTNULL DEFAULT '0'",
                                                       'multipleselect'      => "I NOTNULL DEFAULT '0'",
                                                       'multipleselectcount' => "I NOTNULL DEFAULT '0'",
                                                       'voteauthtype'        => "I NOTNULL DEFAULT '0'",
                                                       'tiebreakalg'         => "I NOTNULL DEFAULT '0'",
                                                       'language'            => "C(30) NOTNULL DEFAULT ''");

    // Enable categorization services
    $pntable['advanced_polls_desc_db_extra_enable_categorization'] = pnModGetVar('advanced_polls', 'enablecategorization');
    $pntable['advanced_polls_desc_primary_key_column'] = 'pollid';

    // add standard data fields
    ObjectUtil::addStandardFieldsToTableDefinition ($pntable['advanced_polls_desc_column'], 'pn_');
    ObjectUtil::addStandardFieldsToTableDataDefinition($pntable['advanced_polls_desc_column_def']);

    // Return the table information
    return $pntable;
}
