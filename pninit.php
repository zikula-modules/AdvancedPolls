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

/**
 * Initialise the Advanced Polls module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return bool true on success, false on failure
 * @since 1.0
*/
function advanced_polls_init() 
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // create tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::createTable($table)) {
            return false;
        }
    }

    // create our default category
    if (!_advanced_polls_createdefaultcategory()) {
        return LogUtil::registerError (__('Error! Creation attempt failed.', $dom));
    }

	// Set up an initial value for each module variable
	pnModSetVar('advanced_polls', 'admindateformat', '_DATETIMEBRIEF');
	pnModSetVar('advanced_polls', 'userdateformat', '_DATETIMEBRIEF');
	pnModSetVar('advanced_polls', 'usereversedns', 0);
	pnModSetVar('advanced_polls', 'scalingfactor', 4);
	pnModSetVar('advanced_polls', 'adminitemsperpage', 25);
	pnModSetVar('advanced_polls', 'useritemsperpage', 25);
    pnModSetVar('advanced_polls', 'defaultcolour', '#000000');
    pnModSetVar('advanced_polls', 'defaultoptioncount', '12');
    pnModSetVar('advanced_polls', 'enablecategorization', true);
    pnModSetVar('advanced_polls', 'addcategorytitletopermalink', true);

	// Initialisation successful
	return true;
}

/**
 * upgrade  the Advanced Polls module from an old version
 * This function can be called multiple times
 *
 * @return bool true on success, false on failure
 * @since 1.0
*/
function advanced_polls_upgrade($oldversion) 
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // update tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::changeTable($table)) {
            return false;
        }
    }

	// Upgrade dependent on old version number
	switch($oldversion) {
		case 1.0:
			// Version 1.0 Didn't have the Module variables
			pnModSetVar('advanced_polls', 'admindateformat', 'r');
			pnModSetVar('advanced_polls', 'userdateformat', 'r');
			pnModSetVar('advanced_polls', 'usereversedns', 0);
			pnModSetVar('advanced_polls', 'scalingfactor', 4);
			return advanced_polls_upgrade(1.1);
		case 1.1:
			// Add additional module variables in this version
			pnModSetVar('advanced_polls', 'adminitemsperpage', 25);
			pnModSetVar('advanced_polls', 'useritemsperpage', 25);
			pnModSetVar('advanced_polls', 'defaultcolour', '#000000');
			pnModSetVar('advanced_polls', 'defaultoptioncount', '12');
			return advanced_polls_upgrade(1.5);
		case 1.5:
            // all changes in this release are covered by the table change
			return advanced_polls_upgrade(1.51);
		case 1.51:
            // populate permalinks for existing content
            $tables = pnDBGetTables();
            $shorturlsep = pnConfigGetVar('shorturlsseparator');            
            $sql  = "UPDATE $tables[advanced_polls_desc] SET pn_urltitle = REPLACE(pn_title, ' ', '{$shorturlsep}')";
            if (!DBUtil::executeSQL($sql)) {
                return LogUtil::registerError (__('Error! Table update failed.', $dom));
            }
			// setup categorisation
            pnModSetVar('advanced_polls', 'enablecategorization', true);
            pnModSetVar('advanced_polls', 'addcategorytitletopermalink', true);
            pnModDBInfoLoad('advanced_polls', 'advanced_polls', true);
            if (!_advanced_polls_createdefaultcategory()) {
                return LogUtil::registerError (__('Error! Update attempt failed.', $dom));
            }
			return advanced_polls_upgrade(2.0);
	}

	// Update successful
	return true;

}
 
/**
 * delete the t the Advanced Polls module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return bool true on success, false on failure
 * @since 1.0
*/
function advanced_polls_delete() 
{
    // delete tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::dropTable($table)) {
            return false;
        }
    }

	// Delete any module variables
	pnModDelVar('advanced_polls');

	// Deletion successful
	return true;
}

/**
 * create the category placeholder
 *
 * @return bool true on success, false on failure
 * @since 2.0
*/
function _advanced_polls_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the language file
    $lang = ZLanguage::getLanguageCode();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $apCat   = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Advanced Polls');

    if (!$apCat) {
        // create placeholder for all our migrated categories
        $cat = new PNCategory ();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'Advanced Polls');
        $cat->setDataField('display_name', array($lang => __('advanced_polls', $dom)));
        $cat->setDataField('display_desc', array($lang => __('Polls', $dom)));
        if (!$cat->validate('admin')) {
            return false;
        }
        $cat->insert();
        $cat->update();
    }

    // get the category path for which we're going to insert our upgraded categories
    $rootcat = CategoryUtil::getCategoryByPath($regpath);
    if ($rootcat) {
        // create an entry in the categories registry
        $registry = new PNCategoryRegistry();
        $registry->setDataField('modname', 'advanced_polls');
        $registry->setDataField('table', 'advanced_polls_desc');
        $registry->setDataField('property', 'Main');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    } else {
        return false;
    }

    return true;
}
