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
class AdvancedPolls_Entity_VotesOld extends Zikula_EntityAccess
{
    
    
    /**
     * The following are annotations which define the voteid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pn_voteid;
    
    
    /**
     * The following are annotations which define the ip field.
     *
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $pn_ip;
        
    /**
     * The following are annotations which define the time field.
     *
     * @ORM\Column(type="string", length=14)
     */
    private $pn_time;
    

    
    /**
     * The following are annotations which define the uid field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_uid;
        
        
    /**
     * The following are annotations which define the voterank field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_voterank;
    
    
    /**
     * The following are annotations which define the pollid field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_pollid;
    
    
    
    /**
     * The following are annotations which define the optionid field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_optionid;
    
    
}