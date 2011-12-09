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

class AdvancedPolls_Installer extends Zikula_AbstractInstaller {

/**
 * Initialise the Advanced Polls module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return bool true on success, false on failure
 */
	public function install()
	{

    	// create tables
    	$tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    	foreach ($tables as $table) {
        	if (!DBUtil::createTable($table)) {
            	return false;
        	}
    	}

    	// create our default category
    	if (!$this->createdefaultcategory()) {
        	return LogUtil::registerError ($this->__('Error! Creation attempt failed.'));
    	}

    	// Set up an initial value for each module variable
    	ModUtil::setVar($this->name, 'usereversedns', 0);
    	ModUtil::setVar($this->name, 'scalingfactor', 4);
    	ModUtil::setVar($this->name, 'cssbars', 1);
    	ModUtil::setVar($this->name, 'adminitemsperpage', 25);
    	ModUtil::setVar($this->name, 'defaultcolour', '#66CC33');
    	ModUtil::setVar($this->name, 'defaultoptioncount', '12');
    	ModUtil::setVar($this->name, 'enablecategorization', true);

    	// Initialisation successful
    	return true;
	}

/**
 * Upgrade  the Advanced Polls module from an old version
 * This function can be called multiple times
 *
 * @return bool true on success, false on failure
 */
public function upgrade($oldversion)
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    // update tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::changeTable($table)) {
            return false;
        }
    }

    switch($oldversion) {
        case '1.0':
            // Version 1.0 Didn't have the Module variables
            ModUtil::setVar($this->name, 'admindateformat', 'r');
            ModUtil::setVar($this->name, 'userdateformat', 'r');
            ModUtil::setVar($this->name, 'usereversedns', 0);
            ModUtil::setVar($this->name, 'scalingfactor', 4);
            return advanced_polls_upgrade('1.1');
        case '1.1':
            // Add additional module variables in this version
            ModUtil::setVar($this->name, 'adminitemsperpage', 25);
            ModUtil::setVar($this->name, 'useritemsperpage', 25);
            ModUtil::setVar($this->name, 'defaultcolour', '#000000');
            ModUtil::setVar($this->name, 'defaultoptioncount', '12');
            return advanced_polls_upgrade('1.5');
        case '1.5':
            // all changes in this release are covered by the table change
            return advanced_polls_upgrade('1.51');
        case '1.51':
            // setup categorisation
            ModUtil::setVar($this->name, 'enablecategorization', true);
            ModUtil::setVar($this->name, 'cssbars', 1);
            ModUtil::setVar($this->name, 'defaultcolour', '#66CC33');
            ModUtil::delVar($this->name, 'admindateformat');
            ModUtil::delVar($this->name, 'userdateformat');
            ModUtil::delVar($this->name, 'useritemsperpage');
            ModUtil::dbInfoLoad($this->name, 'advanced_polls', true);

            //change table: remove votingmethod column
            if (!DBUtil::changeTable('advanced_polls_desc')) {
                return LogUtil::registerError (__('Error! Could not change the advanced polls tables.', $dom));
            }

            // populate permalinks for existing content
            if (!$this->createPermalinks()) {
                return LogUtil::registerError (__('Error! Could not populate permalinks for existing content.', $dom));
            }

            // create the default category
            if (!$this->createdefaultcategory()) {
                return LogUtil::registerError (__('Error! Could not create the default category.', $dom));
            }

            // convert language codes
            if (!$this->updatePollsLanguages()) {
                return LogUtil::registerError (__('Error! Could not convert language codes.', $dom));
            }
            return $this->upgrade('2.0.0');
        case '2.0.0':
            // future upgrade routines
            
        	$this->delVars();
        	
            break;
    }

    // Update successful
    return true;

}

/**
 * Delete the t the Advanced Polls module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 *
 * @return bool true on success, false on failure
 */
public function uninstall()
{
    // delete tables
    $tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
    foreach ($tables as $table) {
        if (!DBUtil::dropTable($table)) {
            return false;
        }
    }

    // Delete any module variables
    $this->delVar($this->name);
    
    // remove category registry entries
    ModUtil::dbInfoLoad('Categories');
    DBUtil::deleteWhere('categories_registry', "modname = 'AdvancedPolls'");    

    // Deletion successful
    return true;
}

/**
 * Create the category placeholder
 *
 * @return bool true on success, false on failure
 */
public function createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
{
    // TODO $dom = ZLanguage::getModuleDomain('advanced_polls');

    // get the language file
    $lang = ZLanguage::getLanguageCode();

    // get the category path for which we're going to insert our place holder category
    $rootcat = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules');
    $apCat   = CategoryUtil::getCategoryByPath('/__SYSTEM__/Modules/Advanced Polls');

    if (!$apCat) {
        // create placeholder for all our migrated categories
        $cat = new Categories_DBObject_Category();
        $cat->setDataField('parent_id', $rootcat['id']);
        $cat->setDataField('name', 'Advanced Polls');
        $cat->setDataField('display_name', array($lang => $this->__('Advanced Polls')));
        $cat->setDataField('display_desc', array($lang => $this->__('Polls')));
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
        $registry = new Categories_DBObject_Registry();
        $registry->setDataField('modname', 'AdvancedPolls');
        $registry->setDataField('table', 'advanced_polls_desc');
        $registry->setDataField('property', 'Main');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    } else {
        return false;
    }

    return true;
}


public function updatePollsLanguages()
{
    $obj = DBUtil::selectObjectArray('advanced_polls_desc');

    if (count($obj) == 0) {
        // nothing to do
        return;
    }

    foreach ($obj as $pollid) {
        // translate l3 -> l2
        if ($l2 = ZLanguage::translateLegacyCode($pollid['language'])) {
            $pollid['language'] = $l2;
        }
        DBUtil::updateObject($pollid, 'advanced_polls_desc', '', 'pollid', true);
    }

    return true;
}


public function createPermalinks()
{
    // get all the ID and permalink of the table
    $data = DBUtil::selectObjectArray('advanced_polls_desc', '', '', -1, -1, 'pollid', null, null, array('pollid', 'title', 'urltitle'));

    // loop the data searching for non equal permalinks
    $perma = '';
    foreach (array_keys($data) as $pollid) {
        $perma = DataUtil::formatPermalink($data[$pollid]['title']);
        if ($data[$pollid]['urltitle'] != $perma) {
            $data[$pollid]['urltitle'] = $perma;
        } else {
            unset($data[$pollid]);
        }
    }

    if (empty($data)) {
        return true;
        // store the modified permalinks
    } elseif (DBUtil::updateObjectArray($data, 'advanced_polls_desc', 'pollid')) {
        // let the calling process know that we have finished successfully
        return true;
    } else {
        return false;
    }
}
}