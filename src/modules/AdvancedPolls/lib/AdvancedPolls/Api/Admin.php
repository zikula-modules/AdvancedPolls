<?php

 class AdvancedPolls_Api_Admin extends Zikula_AbstractApi {

/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

/**
 * Create a new Poll item
 *
 * @return int poll item ID on success, false on failure
 */
public function create($args)
{
    
    // Argument check
    if (!isset($args['title']) || !isset($args['description']) || !isset($args['optioncount'])) {
        return LogUtil::registerArgsError();
    }

    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "{$args['title']}::", ACCESS_ADD)) {
        return LogUtil::registerPermissionError();
    }

    // defaults
    if (!isset($args['language'])) {
        $args['language'] = '';
    }

    // define the permalink title if not present
    if (!isset($args['urltitle']) || empty($args['urltitle'])) {
        $args['urltitle'] = DataUtil::formatPermalink($args['title']);
    }

    if (isset($args['unixopendate'])) {
        // used for duplication a poll
        $args['opendate'] = $args['unixopendate'];
    } else {
        $args['opendate'] = DateUtil::makeTimestamp(DateUtil::buildDatetime($args['startYear'], $args['startMonth'], $args['startDay'], $args['startHour'], $args['startMinute'], 0));
    }

    if (isset($args['unixclosedate'])) {
        // used for duplication a poll
        $args['closedate'] = $args['unixclosedate'];
    } else {
        if (!$args['noclosedate']) {
            $args['closedate'] = DateUtil::makeTimestamp(DateUtil::buildDatetime($args['closeYear'], $args['closeMonth'], $args['closeDay'], $args['closeHour'], $args['closeMinute'], 0));
        } else {
            $args['closedate'] = 0;
        }
    }

    $desc = $this->entityManager->find('AdvancedPolls_Entity_Desc', $pollid);
    $this->entityManager->remove($poll);
    $this->entityManager->flush();
    
    

    // Let any hooks know that we have created a new item.
    ModUtil::callHooks('item', 'create', $args['pollid'], array('module' => 'AdvancedPolls'));

    // An item was created, so we clear all cached pages of the items list.
    $renderer = pnRender::getInstance('AdvancedPolls');
    $renderer->clear_cache('advancedpolls_user_view.htm');

    // Return the id of the newly created item to the calling process
    return $args['pollid'];
}

/**
 * Delete a Poll item
 * @param $args['pollid'] ID of the item
 * @returns bool
 * @return true on success, false on failure
 */
public function delete($args)
{

    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // Get the poll
    $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['pollid']));

    if ($item == false) {
        return LogUtil::registerError ($this->__('Error! No such poll found.'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "{$item['title']}::{$args['pollid']}", ACCESS_DELETE)) {
        return LogUtil::registerPermissionError();
    }

    // Delete the object
    
    $poll = $this->entityManager->find('AdvancedPolls_Entity_Desc', $args['pollid']);
    $this->entityManager->remove($poll);
    $this->entityManager->flush();
    

    return true;
}


/**
 * Reset vote counts to zero
 * @param $args['pollid'] poll id for vote reset
 * @returns bool
 * @return true on success, false on failure
 */
public function resetvotes($args)
{
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['pollid']));

    // check for no such poll return from api function
    if ($item == false) {
        return LogUtil::registerError($this->__('Error! No such poll found.'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    } else {
        $votes = $this->entityManager->getRepository('AdvancedPolls_Entity_Votes2')
                                 ->findBy(array('pollid' => $args['pollid']));
        foreach($votes as $vote) {
            $this->entityManager->remove($vote);
            $this->entityManager->flush();
        }
    }

    return true;
}

/**
 * Get full admin info on all votes
 * @param $args['pollid'] poll id for vote reset
 * @param $args['sortorder'] ascending or desecending sort order
 * @param $args['sortby'] sort field
 * @returns array
 * @return array of items, or false on failure
 */
public function getvotes($args)
{
    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // Optional arguments.
    if (!isset($args['startnum'])) {
        $args['startnum'] = 1;
    }
    if (!isset($args['numitems'])) {
        $args['numitems'] = -1;
    }
    if ((!isset($args['startnum']))  || (!isset($args['numitems']))) {
        return LogUtil::registerArgsError();
    }

    if (!isset($args['sortorder'])) {
        $args['sortorder'] = 0;
    }
    if (!isset($args['sortby'])) {
        $args['sortby'] = 1;
    }

    // The user API function is called.
    $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $args['pollid']));

    // check for no such poll return from api function
    if ($item == false) {
        return LogUtil::registerError($this->__('Error! No such poll found.'));
    }

    // Security check
    if (!SecurityUtil::checkPermission('AdvancedPolls::item', "{$item['title']}::{$args['pollid']}", ACCESS_EDIT)) {
        return LogUtil::registerPermissionError();
    } else {
        // get database setup
        $em = $this->getService('doctrine.entitymanager');
        $qb = $em->createQueryBuilder();
        $qb->select('v')
           ->from('AdvancedPolls_Entity_Votes', 'v');

        switch ($args['sortby']) {
            case 1:
                $sortstring = 'v.voteid';
                break;
            case 2:
                $sortstring = 'v.ip';
                break;
            case 3:
                $sortstring = 'v.time';
                break;
            case 4:
                $sortstring ='v.uid';
                break;
            case 5:
                $sortstring = 'v.voterank';
                break;
            case 6:
                $sortstring = 'v.optionid';
                break;
            default:
                $sortstring = '';
        }
        
        

        if ($args['sortorder'] == 1 ) {
            $sortstring = $sortstring . ' DESC';
        }
        $qb->orderBy($sortstring);

        $qb->where('v.pollid = :pollid')
           ->setParameter('pollid', $args['pollid']);
        
                
        $query = $qb->getQuery();
        $votes = $query->setFirstResult($args['startnum']-1)
                       ->setMaxResults($args['numitems'])
                       ->getArrayResult();
        

        // Check for an error with the database code, and if so set an appropriate
        // error message and return
        if ($votes === false) {
            return LogUtil::registerError ($this->__('Error! Could not load votes.'));
        }
    }

    return $votes;
}

/**
 * Duplicate a poll
 * @param $args['pollid'] poll id to duplicate
 * @returns bool
 * @return true on success, false on failure
 */
public function duplicate($args)
{

    // Argument check
    if (!isset($args['pollid'])) {
        return LogUtil::registerArgsError();
    }

    // The user API function is called.
    $item = $this->entityManager->getRepository('AdvancedPolls_Entity_Desc')
                                 ->find($args['pollid']);

    // check for no such poll return from api function
    if ($item == false) {
        return LogUtil::registerError($this->__('Error! No such poll found.'));
    }

    // Security check
    //if (!SecurityUtil::checkPermission('AdvancedPolls::item', "{$item['title']}::{$args['pollid']}", ACCESS_ADD)) {
      //  return LogUtil::registerPermissionError();
    //} else {
        
        $new_item = clone $item;
        $new_item->set(null, 'pollid');
        $this->entityManager->persist($new_item);
        $this->entityManager->flush();
        

        // The return value of the function is checked
        /*(if ($result = false) {
            LogUtil::registerError ($this->__('Error! Creation attempt failed.'));
        }*/
        return true; //(bool)$result;
    //}
}

/**
 * Get available admin panel links
 *
 * @return array array of admin links
 */
public function getlinks()
{

    $links = array();
    
    
    if (SecurityUtil::checkPermission($this->name . '::', '::', ACCESS_READ)) {
        $links[] = array('url'   => ModUtil::url($this->name, 'user', 'main'),
                'text'  => $this->__('Frontend'),
                'title' => $this->__('Switch to user area.'),
                'class' => 'z-icon-es-home');
    }
    if (SecurityUtil::checkPermission($this->name, '::', ACCESS_READ)) {
        $links[] = array('url' => ModUtil::url('AdvancedPolls', 'admin', 'view'),
                         'text' => $this->__('View Polls'),
                        'class' => 'z-icon-es-view'
                   );
    } 
    if (SecurityUtil::checkPermission($this->name, '::', ACCESS_READ)) {
        $links[] = array('url' => ModUtil::url('AdvancedPolls', 'admin', 'modify'),
                         'text' => $this->__('Create new poll'),
                         'class' => 'z-icon-es-new',
                   );
    } 
    if (SecurityUtil::checkPermission($this->name, '::', ACCESS_ADMIN)) {
        $links[] = array('url' => ModUtil::url('AdvancedPolls', 'admin', 'modifyconfig'),
                         'text' => $this->__('Settings'),
                         'class' => 'z-icon-es-config',
                   );
    }

    return $links;
}
}