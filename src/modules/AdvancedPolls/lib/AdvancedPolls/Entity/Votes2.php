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
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * AddressBook entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="advancedpolls_votes")
 */
class AdvancedPolls_Entity_Votes2 extends Zikula_EntityAccess
{
    
    
    /**
     * The following are annotations which define the voteid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $voteid;
    
   
    /**
     * @ORM\ManyToOne(targetEntity="AdvancedPolls_Entity_Options", inversedBy="votes")
     * @ORM\JoinColumn(name="optionid", referencedColumnName="optionid")
     */
    private $entity;
    
       
    /**
     * The following are annotations which define the ip field.
     *
     * @ORM\Column(type="string", length=20)
     */
    private $ip = '';
    
    
    /**	
     * @ORM\Column(type="datetime")	
     * @Gedmo\Timestampable(on="create")	
     */	
    private $time;
   
    
    
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
     * The following are annotations which define the optionid field.
     *
     * @ORM\Column(type="integer")
     */
    private $pollid = 0;
    
   

    
    public function set($value, $column) {
        $this->$column = $value;
    }
    
    
    public function setAll($options) {
        foreach ($options as $key => $value) {
            $this->$key = $value;
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
            'optionid' => $this->entity,
         );
        
    }
    
    
    
    public function __construct($votes, $entity)
    {
        $this->setAll($votes);
        $this->entity = $entity;
    }
    
    
}