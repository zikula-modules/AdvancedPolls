<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
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
            
            // create the table
            try {
                DoctrineHelper::createSchema($this->entityManager, array(
                    'AdvancedPolls_Entity_Desc', 
                    'AdvancedPolls_Entity_Options',
                    'AdvancedPolls_Entity_Votes2'
                ) );
            } catch (Exception $e) {
                LogUtil::registerStatus($e->getMessage());
                return false;
            }    



            // create our default category
            /*if (!$this->createdefaultcategory()) {
                return LogUtil::registerError ($this->__('Error! Creation attempt failed.'));
            }*/

            // Set up an initial value for each module variable
            $this->setVar('usereversedns', 0);
            $this->setVar('scalingfactor', 4);
            $this->setVar('cssbars', 1);
            $this->setVar('adminitemsperpage', 25);
            $this->setVar('defaultcolour', '66CC33');
            $this->setVar('defaultoptioncount', '12');
            $this->setVar('enablecategorization', true);

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
        $dom = ZLanguage::getModuleDomain('AdvancedPolls');

        // update tables
        /*$tables = array('advanced_polls_votes', 'advanced_polls_data', 'advanced_polls_desc');
        foreach ($tables as $table) {
            if (!DBUtil::changeTable($table)) {
                return false;
            }
        }*/

        switch($oldversion) {
            case '1.0':
                // Version 1.0 Didn't have the Module variables
                $this->setVar('admindateformat', 'r');
                $this->setVar('userdateformat', 'r');
                $this->setVar('usereversedns', 0);
                $this->setVar('scalingfactor', 4);
                return '1.1';
            case '1.1':
                // Add additional module variables in this version
                $this->setVar('adminitemsperpage', 25);
                $this->setVar('useritemsperpage', 25);
                $this->setVar('defaultcolour', '#000000');
                $this->setVar('defaultoptioncount', '12');
                return '1.5';
            case '1.5':
                // all changes in this release are covered by the table change
                return '1.51';
            case '1.51':
                // setup categorisation
                $this->setVar('enablecategorization', true);
                $this->setVar('cssbars', 1);
                $this->setVar('defaultcolour', '#66CC33');
                $this->detVar( 'admindateformat');
                $this->detVar( 'userdateformat');
                $this->detVar( 'useritemsperpage');
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
                // get the values of module vars            
                    $usereveredns = ModUtil::getVar($this->name, 'usereversedns');
                    $scalingfactor = ModUtil::getVar($this->name, 'scalingfactor');
                    $cssbars = ModUtil::getVar($this->name, 'cssbars');
                    $adminitemsperpage = ModUtil::getVar($this->name, 'adminitemsperpage');
                    $defaultcolour = ModUtil::getVar($this->name, 'defaultcolour');
                    $defaultoptioncount = ModUtil::getVar($this->name, 'defaultoptioncount');
                    $enablecategorization = ModUtil::getVar($this->name, 'enablecategorization');        	

                    $this->delVars();

                    // Set up an initial value for each module variable

                    $this->setVar('usereversedns', $usereveredns);
                    $this->setVar('scalingfactor', $scalingfactor);
                    $this->setVar('cssbars', $cssbars);
                    $this->setVar('adminitemsperpage', $adminitemsperpage);
                    $this->setVar('defaultcolour', $defaultcolour);
                    $this->setVar('defaultoptioncount', $defaultoptioncount);
                    $this->setVar('enablecategorization', $enablecategorization);

                    return $this->upgrade('2.0.1');
            case '2.0.1':
                    $this->upgrade3();
                    break;
        }

        // Update successful
        return true;

    }
    
    public function upgrade3()
    {
        // rename old tables, if prefix exist in the system config for legacy modules
        $prefix = $this->serviceManager['prefix'];
        if (!empty($prefix)) {
            $sqlQueries = array();
            $sqlQueries[] = 'RENAME TABLE ' . $prefix . '_advanced_polls_desc' . " TO advanced_polls_desc";
            $sqlQueries[] = 'RENAME TABLE ' . $prefix . '_advanced_polls_data' . " TO advanced_polls_data";
            $sqlQueries[] = 'RENAME TABLE ' . $prefix . '_advanced_polls_votes' . " TO advanced_polls_votes";
            $connection = Doctrine_Manager::getInstance()->getConnection('default');
            foreach ($sqlQueries as $sql) {
                $stmt = $connection->prepare($sql);
                try {
                    $stmt->execute();
                } catch (Exception $e) {
                }   
            }
        }
        
        // create the table
        try {
            DoctrineHelper::createSchema($this->entityManager, array(
                'AdvancedPolls_Entity_Desc', 
                'AdvancedPolls_Entity_Options',
                'AdvancedPolls_Entity_Votes2'
            ) );
        } catch (Exception $e) {
            LogUtil::registerStatus($e->getMessage());
            return false;
        } 
        
        
        $polls = $this->entityManager->getRepository('AdvancedPolls_Entity_DescOld')
                                 ->findAll(); 
        foreach($polls as $poll) {
            
            // get poll data
            $polldata = $poll->getAll();
            if (substr(empty($polldata['opendate']) || $polldata['opendate'], 0, 10) == '1970-01-01') {
                $opendate = null;
            } else {
                $opendate = $polldata['opendate'];
                $polldata['opendate'] = new DateTime();
                $polldata['opendate']->setTimestamp($opendate);
            }
            if (empty($polldata['closedate']) || substr($polldata['closedate'], 0, 10) == '1970-01-01') {
                $polldata['closedate'] = null;
            } else {
                $closedate = $polldata['closedate'];
                $polldata['closedate'] = new DateTime();
                $polldata['closedate']->setTimestamp($closedate);
            }
            $pollid = $polldata['pollid'];
            
            
            // get options data
            $em = $this->getService('doctrine.entitymanager');
            $qb = $em->createQueryBuilder();
            $qb->select('o.pn_optiontext as optiontext, o.pn_optioncolour as optioncolour, o.pn_optionid as optionid')
            ->from('AdvancedPolls_Entity_OptionsOld', 'o')
            ->where('o.pn_pollid = :pollid and o.pn_optiontext != \'\'')
            ->setParameter('pollid', $pollid);
            $options = $qb->getQuery()->getArrayResult();
            foreach($options as $option) {
                $option['optioncolour'] = str_replace('#', '', $option['optioncolour']);
                $optionid = $option['optionid'];
                unset($option['optionid']);
                
                
                // get votes
                $em = $this->getService('doctrine.entitymanager');
                $qb = $em->createQueryBuilder();
                $qb->select('v.pn_ip as ip, v.pn_time as time, v.pn_uid as uid, v.pn_voterank as voterank, v.pn_pollid as pollid')
                ->from('AdvancedPolls_Entity_VotesOld', 'v')
                ->where('v.pn_pollid = :pollid and v.pn_optionid = :optionid')
                ->setParameter('pollid',   $pollid)
                ->setParameter('optionid', $optionid);
                $votes = $qb->getQuery()->getArrayResult();
                foreach ($votes as $vote) {
                    // add votes to option
                    $time = $vote['time'];
                    $vote['time'] = new DateTime();
                    $vote['time']->setTimestamp($time);
                    $closedate = $polldata['closedate'];
                    $option['votes'][] = $vote;
                }

                
                // add otions to poll
                $polldata['options'][] = $option;
            }
            
            $poll = new AdvancedPolls_Entity_Desc();
            $poll->setAll($polldata);
            $this->entityManager->persist($poll);
            $this->entityManager->flush();
            
            /*DoctrineHelper::dropSchema($this->entityManager, array(
                'AdvancedPolls_Entity_DescOld', 
                'AdvancedPolls_Entity_OptionsOld',
                'AdvancedPolls_Entity_VotesOld'
            ));*/
        }

        
        
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

        // drop table
        DoctrineHelper::dropSchema($this->entityManager, array('AdvancedPolls_Entity_Desc', 
                                                               'AdvancedPolls_Entity_Options',
                                                               'AdvancedPolls_Entity_Votes2'));

        // remove all module vars
        $this->delVars();

        // remove category registry entries
        //ModUtil::dbInfoLoad('Categories');
        //DBUtil::deleteWhere('categories_registry', "modname = 'AdvancedPolls'");    

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
        // TODO $dom = ZLanguage::getModuleDomain('AdvancedPolls');

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