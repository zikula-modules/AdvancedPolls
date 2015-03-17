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
 * @ORM\Table(name="advancedpolls_polls")
 */
class AdvancedPolls_Entity_Desc extends Zikula_EntityAccess
{
    

    
    /**
     * The following are annotations which define the pollid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $pollid;
    
        
    /**
     * The following are annotations which define the title field.
     *
     * @ORM\Column(type="string", length=100)
     */
    private $title = '';
        
    /**
     * The following are annotations which define the urltitle field.
     *
     * @ORM\Column(type="string", length=120)
     */
    private $urltitle = '';
    
    
    /**
     * The following are annotations which define the description field.
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $description = null;
    
    
    /**
     * options
     *
     * @ORM\OneToMany(targetEntity="AdvancedPolls_Entity_Options", 
     *                mappedBy="entity", cascade={"all"}, 
     *                orphanRemoval=true)
     */
    public $options;
    
        
    public function setOptions($options) {
                
        foreach ($this->options as $key => $value) {
            if(array_key_exists($key, $options)) {
                $this->options[$key]->setAll($options[$key]);
                unset($options[$key]);
            } else {
                $this->options->remove($key);
            }
            
        }
        
        foreach ($options as $key => $value) {
            $this->options[] = new AdvancedPolls_Entity_Options($value, $this);
        }
             
    }
    
    
    /**
     * The following are annotations which define the opendate field.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $opendate = null;
    
    
    
    /**
     * The following are annotations which define the closedate field.
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $closedate = null;
    


    /**
     * The following are annotations which define the recurring field.
     *
     * @ORM\Column(type="integer")
     */
    private $recurring = 0;
    
    /**
     * The following are annotations which define the recurringoffset field.
     *
     * @ORM\Column(type="integer")
     */
    private $recurringoffset = 0;
    
    /**
     * The following are annotations which define the recurringinterval field.
     *
     * @ORM\Column(type="integer")
     */
    private $recurringinterval = 0;
            
    
    /**
     * The following are annotations which define the multipleselect field.
     *
     * @ORM\Column(type="integer")
     */
    private $multipleselect = 0;
    
    
    /**
     * The following are annotations which define the multipleselectcount field.
     *
     * @ORM\Column(type="integer")
     */
    private $multipleselectcount = 0;
    
    
    /**
     * The following are annotations which define the voteauthtype field.
     *
     * @ORM\Column(type="integer")
     */
    private $voteauthtype = 0;
    
    
    /**
     * The following are annotations which define the tiebreakalg field.
     *
     * @ORM\Column(type="integer")
     */
    private $tiebreakalg = 0;
    
    
    /**
     * The following are annotations which define the language field.
     *
     * @ORM\Column(type="string", length=30)
     */
    private $language = '';
    
    
    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="create")
     */
    private $cr_uid;
    
    
    
    
    /**
     * @ORM\Column(type="integer")
     * @ZK\StandardFields(type="userid", on="update")
     */
    private $lu_uid;
    
    
    
    /**	
     * @ORM\Column(type="datetime")	
     * @Gedmo\Timestampable(on="create")	
     */	
    private $cr_date;
    
    
     /**	
      * @ORM\Column(type="datetime")	
      * @Gedmo\Timestampable(on="update")	
      */	
    private $lu_date;
    
    
    
    
    public function set($value, $column) {
        $this->$column = $value;
    }
    
    
    
    public function setAll($data) {
        foreach($data as $key => $value) {
            if($key == 'opendate' or  $key == 'closedate') {
                if(is_string($value) ) {
                    $value = new DateTime($value);
                }
            } else if($key == 'options') {
                $this->setOptions($value);
                continue;
            }
            $this->set($value, $key);
        }
    }
    
    
    public function getAll() {

        return array(
            'pollid'              => $this->pollid,
            'title'               => $this->title,
            'urltitle'            => $this->urltitle,    
            'description'         => $this->description,
            'opendate'            => $this->opendate,
            'closedate'           => $this->closedate,
            'recurring'           => $this->recurring,
            'recurringoffset'     => $this->recurringoffset,
            'recurringinterval'   => $this->recurringinterval,
            'multipleselect'      => $this->multipleselect,
            'multipleselectcount' => $this->multipleselectcount,
            'voteauthtype'        => $this->voteauthtype,
            'tiebreakalg'         => $this->tiebreakalg,
            'language'            => $this->language,
            'cr_uid'              => $this->cr_uid,
            'cr_date'             => $this->cr_date,
            'lu_uid'              => $this->lu_uid,
            'lu_date'             => $this->lu_date
         );
        
    }
    
    public function __construct()
    {
        $this->options = new Doctrine\Common\Collections\ArrayCollection();
    }

    
}