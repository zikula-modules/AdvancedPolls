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
use DoctrineExtensions\StandardFields\Mapping\Annotation as ZK;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;



/**
 * AddressBook entity class.
 *
 * Annotations define the entity mappings to database.
 *
 * @ORM\Entity
 * @ORM\Table(name="advanced_polls_desc")
 */
class AdvancedPolls_Entity_DescOld extends Zikula_EntityAccess
{
    

    
    /**
     * The following are annotations which define the pollid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pn_pollid;
    
        
    /**
     * The following are annotations which define the title field.
     *
     * @ORM\Column(type="string", length=100)
     */
    private $pn_title;
        
    /**
     * The following are annotations which define the urltitle field.
     *
     * @ORM\Column(type="string", length=120)
     */
    private $pn_urltitle;
    
    
    /**
     * The following are annotations which define the description field.
     *
     * @ORM\Column(type="text", nullable="true")
     */
    private $pn_description;
    
    
    /**
     * The following are annotations which define the opendate field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_optioncount;
    
    
    
    /**
     * The following are annotations which define the opendate field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_opendate;
    
    
    
    /**
     * The following are annotations which define the closedate field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_closedate;
    


    /**
     * The following are annotations which define the recurring field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_recurring;
    
    /**
     * The following are annotations which define the recurringoffset field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_recurringoffset;
    
    /**
     * The following are annotations which define the recurringinterval field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_recurringinterval;
            
    
    /**
     * The following are annotations which define the multipleselect field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_multipleselect;
    
    
    /**
     * The following are annotations which define the multipleselectcount field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_multipleselectcount;
    
    
    /**
     * The following are annotations which define the voteauthtype field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_voteauthtype;
    
    
    /**
     * The following are annotations which define the tiebreakalg field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_tiebreakalg;
    
    
    /**
     * The following are annotations which define the language field.
     *
     * @ORM\Column(type="string", length=30)
     */
    private $pn_language;
    
    
    /**
     * The following are annotations which define the language field.
     *
     * @ORM\Column(type="string", length=30)
     */
    private $pn_obj_status;
    
    
    
    /**
     * @ORM\Column(type="integer")
     */
    private $pn_cr_uid;
    
    
    
    
    /**
     * @ORM\Column(type="integer")
     */
    private $pn_lu_uid;
    
    
    
    /**	
     * @ORM\Column(type="datetime")	
     */	
    private $pn_cr_date;
    
    
     /**	
      * @ORM\Column(type="datetime")	
      */	
    private $pn_lu_date;
    
    
    
    public function getAll() {

        return array(
            'pollid'              => $this->pn_pollid,
            'title'               => $this->pn_title,
            'urltitle'            => $this->pn_urltitle,    
            'description'         => $this->pn_description,
            'optioncount'         => $this->pn_optioncount,
            'opendate'            => $this->pn_opendate,
            'closedate'           => $this->pn_closedate,
            'recurring'           => $this->pn_recurring,
            'recurringoffset'     => $this->pn_recurringoffset,
            'recurringinterval'   => $this->pn_recurringinterval,
            'multipleselect'      => $this->pn_multipleselect,
            'multipleselectcount' => $this->pn_multipleselectcount,
            'voteauthtype'        => $this->pn_voteauthtype,
            'tiebreakalg'         => $this->pn_tiebreakalg,
            'language'            => $this->pn_language,
            'obj_status'          => $this->pn_obj_status,
            'cr_uid'              => $this->pn_cr_uid,
            'cr_date'             => $this->pn_cr_date,
            'lu_uid'              => $this->pn_lu_uid,
            'lu_date'             => $this->pn_lu_date
         );
        
    }
    
        
    public function __construct()
    {
        $this->options = new Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    
}