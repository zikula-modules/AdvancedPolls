<?php

/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * AddressBook entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="advanced_polls_votes")
 */
class AdvancedPolls_Entity_Votes extends Zikula_EntityAccess
{
    
    
    /**
     * The following are annotations which define the voteid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $voteid;
    
    
    
    // personal
    
    /**
     * The following are annotations which define the ip field.
     *
     * @ORM\Column(type="string", length=20, nullable="true")
     */
    private $ip = null;
        
    /**
     * The following are annotations which define the time field.
     *
     * @ORM\Column(type="string", length=14)
     */
    private $time = '';
    
   
    
    
    /**
     * The following are annotations which define the uid field.
     *
     * @ORM\Column(type="integer")
     */
    private $uid = 0;
        
        
    /**
     * The following are annotations which define the voterank field.
     *
     * @ORM\Column(type="integer")
     */
    private $voterank = 0;
    
    
    /**
     * The following are annotations which define the pollid field.
     *
     * @ORM\Column(type="integer")
     */
    private $pollid = 0;
    
    
    
    /**
     * The following are annotations which define the optionid field.
     *
     * @ORM\Column(type="integer")
     */
    private $optionid = 0;
    
   

    
    public function set($value, $column) {
        $this->$column = $value;
    }
    
    
    public function setAll($data) {
        foreach($data as $key => $value) {           
            $this->set($value, $key);
        }
    }
    

    public function getAll() {

        return array(
            'voteid'   => $this->voteid,
            'ip'       => $this->ip,
            'time'     => $this->time,    
            'uid'      => $this->uid,
            'voterank' => $this->voterank,
            'pollid'   => $this->pollid,
            'optionid' => $this->optionid,
         );
        
    }
    
        
    public function __construct()
    {
        $this->categories = new Doctrine\Common\Collections\ArrayCollection();
    }
    
    
}