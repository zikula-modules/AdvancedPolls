<?php

/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class AdvancedPolls_Api_User extends Zikula_AbstractApi {



    /**
     * Get all poll items
     * @param $args['startnum'] starting poll id
     * @param $args['numitems'] number of polls to get
     * @param $args['checkml'] flag to check ml status
     * @param $args['desc'] array title key name
     * @return mixed array of items, or false on failure
     */
    public function getall($args)
    {

        if (!isset($args['checkml'])) {
            $args['checkml'] = true;
        }
        if (isset($args['desc']) && $args['desc']) {
            $args['desc'] = 'DESC';
        } else {
            $args['desc'] = 'ASC';
        }
        
        $items = array();

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        /*$args['catFilter'] = array();
        if (isset($args['category']) && !empty($args['category'])){
            if (is_array($args['category'])) {
                $args['catFilter'] = $args['category'];
            } elseif (isset($args['property'])) {
                $property = $args['property'];
                $args['catFilter'][$property] = $args['category'];
            }
        }*/

        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('d')
           ->from('AdvancedPolls_Entity_Desc', 'd')
           ->orderBy('d.pollid', $args['desc']);
        
        
        if (System::getVar('multilingual') == 1 && $args['checkml']) {
            $qb->where("(d.language = :language OR d.language = '')")
               ->setParameter('language', DataUtil::formatForStore(ZLanguage::getLanguageCode()));
        }


        // define the permission filter to apply
        /*$permFilter = array(array('realm'           => 0,
                                'component_left'  => 'AdvancedPolls',
                                'component_right' => 'item',
                                'instance_left'   => 'title',
                                'instance_right'  => 'pollid',
                                'level'           => ACCESS_READ));*/

        // get the objects from the db
                
        $query = $qb->getQuery();
        
        
        
        // Optional arguments.
        if (isset($args['startnum']) AND !empty($args['startnum'])) {
            $query->setFirstResult($args['startnum']);
        }
        if (isset($args['numitems']) AND !empty($args['numitems'])) {
            $query->setMaxResults($args['numitems']);
        }
        
        
        $items = $query->getArrayResult();
        
                
        //ToDo: $permFilter
        //ToDo: $args['catFilter']);

        
        // Check for an error with the database code, and if so set an appropriate
        // error message and return
        if ($items === false) {
            return LogUtil::registerError ($this->__('Error! Could not load polls.'));
        }

        // need to do this here as the category expansion code can't know the
        // root category which we need to build the relative path component
        /*if ($items && isset($args['catregistry']) && $args['catregistry']) {
            if (!($class = Loader::loadClass ('CategoryUtil'))) {
                pn_exit ($this->__f('Error! Unable to load class [%s]', array('s' => 'CategoryUtil')));
            }
            ObjectUtil::postProcessExpandedObjectArrayCategories ($items, $args['catregistry']);
        }*/

        // Return the items
        return $items;
    }

    /**
    * Get a specific Poll
    * @param $args['pollid'] id of example item to get
    * @param $args['idname'] array id key name
    * @param $args['titlename'] array title key name
    * @param $args['color'] array color key name
    * @param $args['checkml'] flag to check ml status
    * @return mixed item array, or false on failure
    */
    public function get($args)
    {
        // optional arguments
        if (isset($args['objectid'])) {
            $args['pollid'] = $args['objectid'];
        }

        // Argument check
        if ((!isset($args['pollid']) || !is_numeric($args['pollid'])) && !isset($args['title'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        // ToDo: single permission
        if (!SecurityUtil::checkPermission('AdvancedPolls::', '::', ACCESS_OVERVIEW)) {
            return false;
        }
        
        
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('d, o, v')
           ->from('AdvancedPolls_Entity_Desc', 'd')
           ->where('d.pollid = :pollid AND (v.pollid = :pollid OR v.pollid IS NULL)')
           ->setParameter('pollid', $args['pollid'] )
           ->leftJoin('d.options', 'o')
           ->leftJoin('o.votes', 'v');
        
        $query = $qb->getQuery();
        $poll = $query->getArrayResult();
        
        
        if(count($poll) < 1) {
            LogUtil::registerStatus($this->__('No poll found!'));
        }
        
        $poll = $poll[0];
        
        $poll['number_of_votes'] = 0;
        foreach($poll['options'] as $option) {
            $poll['number_of_votes'] += count($option['votes']);
        }

        // Return the item array
        return $poll;
    }

    /**
    * Utility function to count the number of items held by this module
    * @param $args['checkml'] flag to check ml status
    * @return integer number of items held by this module
    */
    public function countitems($args)
    {
        $args['catFilter'] = array();
        if (isset($args['category']) && !empty($args['category'])){
            if (is_array($args['category'])) {
                $args['catFilter'] = $args['category'];
            } elseif (isset($args['property'])) {
                $property = $args['property'];
                $args['catFilter'][$property] = $args['category'];
            }
        }

        // defauls
        if (!isset($checkml)) {
            $checkml = true;
        }

        
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('d')
           ->from('AdvancedPolls_Entity_Desc', 'd')
           ->orderBy('d.optionid');
        
        
        
        
        
        // Check if we is an ML situation
        $querylang = '';
        if ($checkml && System::getVar('multilingual') == 1) {
            $qb->where("(d.language = :language OR d.language = '' OR ".
                       "d.language IS NULL)")
            ->setParameter('language', ZLanguage::getLanguageCode());
        }
        
        //TODO  $args['catFilter']
        $query = $qb->getQuery();
        $items = $query->getArrayResult();
        // Return the number of items
        return count($items);
        
        
    }

    /**
    * Check if poll is open
    * @param $args['pollid'] id of example item to get
    * @return bool false if closed, true if open
    */
    public function isopen($args)
    {
        // Argument check
        if (!isset($args['pollid']) and !isset($args['item'])) {
            return LogUtil::registerArgsError();
        }
        
        if (isset($args['item'])) {
            $item = $args['item'];
        } else {
            // The user API function is called.
            $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['pollid']));
        }

        // no such item is db
        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such poll found.'));
        }

        
        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', $item['title'].'::'.$item['pollid'], ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        //establish current date and time
        $currentdate = new DateTime();

        //establish poll open date and time
        $opendate = $item['opendate'];

        //establish poll close date and time
        $closedate = $item['closedate'];

        //is poll open?
        if (($currentdate >= $opendate) && (($currentdate <= $closedate) || $closedate == null)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if user has voted in poll
     * @param $args['pollid'] id of poll item to get
     * @return bool true on vote allowed, false on vote not allowed
     */
    public function isvoteallowed($args)
    {
        // Argument check
        if (!isset($args['pollid']) and !isset($args['item'])) {
            return LogUtil::registerArgsError();
        }

        if (isset($args['item'])) {
            $item = $args['item'];
        } else {
            // The user API function is called.
            $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['pollid']));
        }

        // no such item in db
        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such poll found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', $item['title'].'::'.$item['pollid'], ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', $item['title'].'::'.$item['pollid'], ACCESS_COMMENT)) {
            // Here we don't set an error as this indicates that the user can't vote in this poll
            return false;
        }

        // get voting authorisation from item array
        $voteauthtype = $item['voteauthtype'];
        

        switch ($voteauthtype) {
            case 0: //Legacy - should not be used
            case 1: //Free voting
                // voting always allowed
                return true;
            case 2: //UID Voting
                // // extract user id from session variables
                $uid = UserUtil::getVar('uid');
                
                // get all the matching votes
                $em = $this->getService('doctrine.entitymanager');
                $qb = $em->createQueryBuilder();
                $qb->select('v')
                   ->from('AdvancedPolls_Entity_Votes', 'v')
                   ->where('v.uid = :uid AND v.pollid = :pollid')
                   ->setParameter('uid', $uid)
                   ->setParameter('pollid', $item['pollid']);
                $query = $qb->getQuery();
                $items = $query->getArrayResult();
                $votes = count($items);

                if ($votes == 0) {
                    return true;
                } else {
                    return false;
                }
            case 3: //Cookie voting
                // check for existance of session variable (cookie)
                // if set then vote is invalid otherwise set session variable
                // and return valid
                if (SessionUtil::getVar("advanced_polls_voted{$item['pollid']}")) {
                    return false;
                } else {
                    return true;
                }
            case 4: //IP address voting
                // extract ip from http headers
                $ip = $_SERVER['REMOTE_ADDR'];

                // get all the matching votes
                $em = $this->getService('doctrine.entitymanager');
                $qb = $em->createQueryBuilder();
                $qb->select('v')
                   ->from('AdvancedPolls_Entity_Votes2', 'v')
                   ->where('v.ip = :ip AND v.pollid = :pollid')
                   ->setParameter('ip', $ip)
                   ->setParameter('pollid', $item['pollid']);
                $query = $qb->getQuery();
                $items = $query->getArrayResult();
                $votes = count($items);
                //If there are no rows back from this query then this uid can vote
                if ($votes == 0) {
                    return true;
                } else {
                    return false;
                }
            case 5: //Cookie + IP address voting
                // possibly remove this voting style
                return true;
            default: //any other option - should never occur
                return LogUtil::registerError($this->__('Error! No poll authorisation method.'));
        }
    }

    /**
    * Reset polls votes if poll is recurring poll
    * @param $args['pollid'] id of example item to get
    * @return bool true on reset success, false on vote failure
    */
    public function resetrecurring($args)
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($pollid)) {
            return LogUtil::registerArgsError();
        }

        // The user API function is called.
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

        // check for no such poll return from api function
        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such poll found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_READ)) {
            return LogUtil::registerPermissionError();
        }

        // convert recurring offset into unix timestamp format
        $offset = $item['recurringoffset'] * 60 * 60;
        $closetimewithoffset = $item['closedate'] + $offset;

        // if this poll is currently closed and poll it set to reoccur
        // then update relevant db tables
        // Doesn't call IsPollOpen API as this checks for both before
        // poll open date and after poll close date
        // We are only insterest in Poll close date
        $currentDate = new DateTime();
        if (($closetimewithoffset < $currentDate) and ($item['recurring'] == 1)) {

            
            $poll = $this->entityManager->find('AdvancedPolls_Entity_Votes', $pollid);
            $this->entityManager->remove($poll);
            $this->entityManager->flush();
            
            
            // set new opening and closing times
            // calculate recurrance interval in seconds from db value in days
            $recurranceinterval = $item['recurringinterval'] * 24 * 60 * 60;

            //new open is close time with offset calculated earlier in this function
            $newopentime = $closetimewithoffset;
            $newclosetime = $item['closedate'] + $recurranceinterval;

            // update poll close and open times
            $obj = array('pollid'    => $pollid,
                        'opendate'  => $newopentime,
                        'closedate' => $newclosetime);

            
            
            $poll = $this->entityManager->find('AdvancedPolls_Entity_Desc', $obj['pollid']);
            $poll->setAll($obj);
            $this->entityManager->persist($poll);
            $this->entityManager->flush();
            

        }
        return true;
    }

    /**
    * Get counts of votes in a poll leading vote and total vote count
    * @param $args['pollid'] id of example item to get
    * @return mixed voting array on success, false on failure
    */
    public function pollvotecount($args)
    {
        
        // Argument check
        if (!isset($args['pollid'])) {
            return LogUtil::registerArgsError();
        }

        // The user API function is called.
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['pollid']));
        if ($item == false) {
            return LogUtil::registerError($his->__('Error! No such poll found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', $item['title'].'::'.$args['pollid'], ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('v')
           ->from('AdvancedPolls_Entity_Votes2', 'v')
           ->where('v.pollid = :pollid')
           ->setParameter('pollid', $args['pollid']);
        $query = $qb->getQuery();
        $items = $query->getArrayResult();
        $totalvotecount = count($items);

        $votecountarray = array();
        $recordcount = 0;

        // Set initial vote id
        $leadingvotecount = 0;
        $leadingvoteid = 0;

        
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(v.optionid) as optioncount, v.optionid')
           ->from('AdvancedPolls_Entity_Votes', 'v')
           ->where('v.pollid = :pollid')
           ->setParameter('pollid', $args['pollid'])
           ->groupBy('v.optionid');
        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        

        if (is_array($result) && !empty($result)) {
            foreach ($result as $row) {
                $votecountarray[$row['optionid']] = $row['optioncount'];
            }
        }
        
        
        for ($i = 1, $max = $item['number_of_votes']; $i <= $max; $i++) {
            if (!isset($votecountarray[$i])) {
                $votecountarray[$i] = 0;
            }
            if (($votecountarray[$i] == $leadingvotecount) and ($item['tiebreakalg']) > 0) {
                if ($item['tiebreakalg'] == 1) {
                    $leadingvoteid = ModUtil::apiFunc($this->name, 'user', 'timecountback',
                    array('pollid'  => $args['pollid'],
                        'voteid1' => $leadingvoteid,
                        'voteid2' => $i));
                }
            }

            if ($votecountarray[$i] > $leadingvotecount) {
                $leadingvotecount = $votecountarray[$i];
                $leadingvoteid = $i;
            }
        }

        // Create the item array
        $item = array('totalvotecount' => $totalvotecount,
                    'leadingvoteid'  => $leadingvoteid,
                    'votecountarray' => $votecountarray);
        
        // Return the item array
        return $item;
    }

    /**
    * Adds vote to db
    * @param $args['pollid'] id of example item to get
    * @param $args['voteid'] poll item to register vote for
    * @param $args['voterank'] ranking of vote in multiple select polls
    * @return bool true on success, false on failure
    */
    public function addvote($args)
    {
        // Argument check
        if (!isset($args['pollid']) || !isset($args['optionid']) || !isset($args['voterank'])) {
            return LogUtil::registerArgsError();
        }

        // Security check
        if (SecurityUtil::checkPermission('AdvancedPolls::item',"{$args['title']}::{$args['pollid']}",ACCESS_COMMENT)) {
            $args['ip'] = $_SERVER['REMOTE_ADDR'];
            $args['uid'] = UserUtil::getVar('uid');            
            
            $poll = new AdvancedPolls_Entity_Votes();
            $poll->setAll($args);
            $this->entityManager->persist($poll);
            $this->entityManager->flush();

            //set cookie to indicate vote made in this poll
            //used only with cookie based voting but set all the time
            //in case admin changes voting regs.
            SessionUtil::setVar("advanced_polls_voted{$args['pollid']}", 1);

            return true;
        }

        return false;
    }

    /**
    * Performs time count back on votes
    *
    * This function sums all the unix timestamps for two poll item ids
    * and returns the item id with the lowest sum. This is used as a tiebreak
    * methodology
    *
    * @param $args['pollid'] id of example item to get
    * @param $args['voteid1'] first poll item id
    * @param $args['voteid2'] second poll item id
    * @return mixed integer $voteid or false on vote failure
    */
    public function timecountback($args)
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($pollid) || !isset($voteid1) || !isset($voteid2)) {
            return LogUtil::registerArgsError();
        }



        
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_OVERVIEW)) {
            return LogUtil::registerPermissionError();
        }

        
        // get database tables
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('SUM(v.time)')
           ->from('AdvancedPolls_Entity_Votes', 'v')
           ->where('v.pollid = :pollid AND v.optionid = :voteid')
           ->setParameter('pollid', $pollid)
           ->setParameter('voteid', $voteid1)     
           ->orderBy('d.optionid');        
        $query = $qb->getQuery();
        $firstsum = $query->getArrayResult();
        
        
       
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('SUM(v.time)')
           ->from('AdvancedPolls_Entity_Votes', 'v')
           ->where('v.pollid = :pollid AND v.optionid = :voteid')
           ->setParameter('pollid', $pollid)
           ->setParameter('voteid', $voteid2)     
           ->orderBy('d.optionid');        
        $query = $qb->getQuery();
        $secondsum = $query->getArrayResult();

        if ($firstsum < $secondsum) {
            return $voteid1;
        } else {
            return $voteid2;
        }
    }

    /**
    * Gets id of last poll to close
    * @return int id of last poll to close, 0 if no closed polls
    */
    public function getlastclosed($args)
    {
        // The API function is called.
        $items = ModUtil::apiFunc($this->name, 'user', 'getall');

        // work out which poll has closed most recently
        $lastclosed = 0;
        $lastcloseddate = 0;
        foreach ($items as $item) {
            $currentDate = new DateTime();
            if ($item['opendate'] < $currentDate && $item['closedate'] < $currentDate && $item['closedate'] != 0 && $item['closedate'] >= $lastcloseddate) {
                $lastclosed = $item['pollid'];
                $lastcloseddate = $item['closedate'];
            }
        }

        return $lastclosed;
    }

    /**
    * Gets a random poll id
    * @return int id of poll
    */
    public function getrandom() {
        // seed with microseconds
        function make_seed() {
            list($usec, $sec) = explode(' ', microtime());
            return (float) $sec + ((float) $usec * 100000);
        }
        srand(make_seed());

        // The API function is called.
        $items = ModUtil::apiFunc($this->name, 'user', 'getall');

        $randomitemid = array_rand($items , 1);
        $randomitem = $items[$randomitemid];
        $pollid = $randomitem['pollid'];

        return $pollid;
    }

    /**
    * Form custom url string
    *
    * @return string custom url string
    */
    public function encodeurl($args)
    {
        // check we have the required input
        if (!isset($args['modname']) || !isset($args['func']) || !isset($args['args'])) {
            return LogUtil::registerArgsError();
        }

        // create an empty string ready for population
        $vars = '';

        // view function
        if ($args['func'] == 'view' && isset($args['args']['cat'])) {
            $vars = substr($args['args']['cat'], 1);
        }


        // for the display function use either the title (if present) or the page id
        if ($args['func'] == 'display' || $args['func'] == 'results') {
            // check for the generic object id parameter
            if (isset($args['args']['objectid'])) {
                $args['args']['pollid'] = $args['args']['objectid'];
            }
            // get the item
            if (isset($args['args']['pollid'])) {
                $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['args']['pollid']));
            } else {
                $item = ModUtil::apiFunc($this->name, 'user', 'get', array('title' => $args['args']['title']));
            }
            $vars = $item['urltitle'];
            if (isset($args['args']['page']) && $args['args']['page'] != 1) {
                $vars .= '/page/'.$args['args']['page'];
            }
        }
        
        
        
        // don't display the function name if either displaying an page or the normal overview
        if ($args['func'] == 'main' || $args['func'] == 'display') {
            $args['func'] = '';
        }

        // construct the custom url part
        if (empty($args['func']) && empty($vars)) {
            return $args['modname'] . '/';
        } elseif (empty($args['func'])) {
            return $args['modname'] . '/' . $vars . '/';
        } elseif (empty($vars)) {
            return $args['modname'] . '/' . $args['func'] . '/';
        } else {
            return $args['modname'] . '/' . $args['func'] . '/' . $vars . '/';
        }
    }

    /**
    * Decode the custom url string
    *
    * @return bool true if successful, false otherwise
    */
    public function decodeurl($args)
    {
        // check we actually have some vars to work with...
        if (!isset($args['vars'])) {
            return LogUtil::registerArgsError();
        }

        // define the available user functions
        $funcs = array('main', 'view', 'display', 'results', 'vote');
        // set the correct function name based on our input
        if (empty($args['vars'][2])) {
            System::queryStringSetVar('func', 'main');
        } elseif (!in_array($args['vars'][2], $funcs)) {
            System::queryStringSetVar('func', 'display');
            $nextvar = 2;
        } else {
            System::queryStringSetVar('func', $args['vars'][2]);
            $nextvar = 3;
        }

        $func = FormUtil::getPassedValue('func');

        // add the category info
        if ($func == 'view') {
            System::queryStringSetVar('cat', (string)$args['vars'][$nextvar]);
        }

        // identify the correct parameter to identify the page
        if ($func == 'display' || $func == 'results') {
            // get rid of unused vars
            $args['vars'] = array_slice($args['vars'], $nextvar);
            $nextvar = 0;
            if (is_numeric($args['vars'][$nextvar])) {
                System::queryStringSetVar('pollid', $args['vars'][$nextvar]);
            } else {
                System::queryStringSetVar('title', $args['vars'][$nextvar]);
            }
        }

        return true;
    }

    /**
    * Get available admin panel links
    *
    * @return array array of admin links
    */
    public function getLinks()
    {

        $links = array();

        if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_ADMIN)) {
                $links[] = array('url'   => ModUtil::url($this->name, 'admin', 'main'),
                    'text'  => $this->__('Backend'),
                    'title' => $this->__('Switch to administration area.'),
                    'class' => 'z-icon-es-options');
            }  
        if (SecurityUtil::checkPermission($this->name, '::', ACCESS_READ)) {
            $links[] = array('url' => ModUtil::url('AdvancedPolls', 'user', 'view'),
                            'text' => $this->__('View Polls'));
        } 

        return $links;
    }
}