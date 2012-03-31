<?php

namespace FDT\doctrineExtensions\NestedSet\Documents;

use  FDT\doctrineExtensions\NestedSet\NodeWrapper;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

//FDT\doctrineExtensions\NestedSet\Documents\BaseNode


interface BaseNode
{    
    public function getLevel();
	            
    public function getId();
    
    public function addAncestor($ancestor);

    public function getAncestors();
    
    public function setParent($ancestor);
    
    public function getParent();
    
    public function setLevel();

    public function getStringForPath();

       
}
