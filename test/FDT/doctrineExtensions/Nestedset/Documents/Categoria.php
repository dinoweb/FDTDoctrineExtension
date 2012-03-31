<?php

namespace Nestedset\Documents;

use FDT\doctrineExtensions\NestedSet\Documents\BaseNode;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;

/** 
 * @MongoDB\Document
 */
class Categoria implements BaseNode
{
     
     /** @MongoDB\Id(strategy="AUTO") */
    private $id;
	    
    /**
     * @MongoDB\Int
     */
    private $level;
     
     /**
     * @MongoDB\ReferenceMany(targetDocument="Categoria", cascade="all")
     * @MongoDB\Index
     */
    private $ancestors = array();
    
    /**
     * @MongoDB\ReferenceOne(targetDocument="Categoria", cascade="all")
     * @MongoDB\Index
     */
    private $parent;
     
          
     /**
     * @MongoDB\String
     */
    private $name;

    /**
     * @MongoDB\Int
     */
    private $ebayId;
    
    /**
     * @MongoDB\Boolean
     */
    private $ebayOfferedEnable;
    
    public function __construct()
    {
        $this->ancestors = new ArrayCollection();
    }
    
    public function getLevel()
    {	
        return $this->level;
    }
	            
    
    public function getId()
    {
        return $this->id;
    }
    
    public function addAncestor($ancestor)
    {
        $this->ancestors[] = $ancestor;
    }

    public function getAncestors()
    {
        return $this->ancestors;
    }
    
    public function setParent($ancestor)
    {       
       $this->parent = $ancestor;
    }
    
    public function getParent()
    {
       return $this->parent;
    }
    
    public function setLevel()
    {	
        $this->level = count ($this->getAncestors());
        
    }    
        
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setEbayId($ebayId)
    {
        $this->ebayId = $ebayId;
    }

    public function getEbayId()
    {
        return $this->ebayId;
    }
    
    public function setEbayOfferedEnable($ebayOfferedEnable)
    {
        $this->ebayOfferedEnable = $ebayOfferedEnable;
    }

    public function getEbayOfferedEnable()
    {
        return $this->ebayOfferedEnable;
    }
    
    public function getStringForPath()
    {
       return $this->getName();
    }

    
    
    
        
}
