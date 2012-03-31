<?php

namespace Timestampable\Documents;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

use FDT\doctrineExtensions\Timestampable\Mapping as FDT;

/** 
 * @MongoDB\Document(collection="articles")
 */
class Article
{
    /** @MongoDB\Id */
    private $id;

    /**
     * @MongoDB\String
     */
    private $title;

    /**
     * @MongoDB\String
     */
    private $code;
    
    /**
     * @MongoDB\String
     */
    private $slug;
    
    /**
     * @FDT:Timestampable(on="create")
     * @MongoDB\Timestamp
     */
    private $createdAt;
    
    /**
     * @FDT:Timestampable(on="update")
     * @MongoDB\Timestamp
     */
    private $updatedAt;

    public function getId()
    {
        return $this->id;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }
    
    public function getSlug()
    {
        return $this->slug;
    }
    
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }
    
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
    
    public function setUpdated(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}
