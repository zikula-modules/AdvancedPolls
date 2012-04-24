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
 * @ORM\Table(name="advancedpolls_options")
 */
class AdvancedPolls_Entity_Options extends Zikula_EntityAccess
{    
    
    /**
     * @ORM\ManyToOne(targetEntity="AdvancedPolls_Entity_Desc", inversedBy="options")
     * @ORM\JoinColumn(name="pollid", referencedColumnName="pollid")
     */
    private $entity;
    
    
    
    /**
     * votes
     *
     * @ORM\OneToMany(targetEntity="AdvancedPolls_Entity_Votes2", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true)
     */
    private $votes;
    
        
    /**
     * The following are annotations which define the optiontext field.
     *
     * @ORM\Column(type="string", length=255, nullable="false")
     */
    private $optiontext;

    /**
     * The following are annotations which define the optionid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $optionid;
        
        
    /**
     * The following are annotations which define the optioncolour field.
     *
     * @ORM\Column(type="string", length="6", nullable="true")
     */
    private $optioncolour = null;
   

    
    public function set($value, $column) {
        $this->$column = $value;
    }
    
    
    public function setAll($options) {
        foreach ($options as $key => $value) {
            if($key == 'votes' ) {
                foreach ($value as $key => $votes) {
                    $this->votes[] = new AdvancedPolls_Entity_Votes2($votes, $this);
                }
            } else {
                $this->$key = $value;
            }
        }
    }
    

    public function getAll() {

        return array(
            'voteid'   => $this->voteid,
            'ip'       => $this->ip,
            'time'     => $this->time,    
            'uid'      => $this->uid,
            'voterank' => $this->voterank,
            'pollid'   => $this->entity,
            'optionid' => $this->optionid,
         );
        
    }
    
        
    public function __construct($value, $entity)
    {
        $this->votes = new Doctrine\Common\Collections\ArrayCollection();
        $this->setAll($value);
        $this->entity = $entity;
    }
    
    
    public function getEntity()
    {
        return $this->entity;
    }
    

    
}