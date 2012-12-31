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
 * @ORM\Table(name="advanced_polls_data")
 */
class AdvancedPolls_Entity_OptionsOld extends Zikula_EntityAccess
{    
    
    /**
     * The following are annotations which define the pollid field.
     *
     * @ORM\Column(type="integer")
     */
    private $pn_pollid;
    
    
        
    /**
     * The following are annotations which define the optiontext field.
     *
     * @ORM\Column(type="string", length=255, nullable="false")
     */
    private $pn_optiontext;

    /**
     * The following are annotations which define the optionid field.
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    private $pn_optionid;
        
        
    /**
     * The following are annotations which define the optioncolour field.
     *
     * @ORM\Column(type="string", length="7")
     */
    private $pn_optioncolour;
    
}