<?php

namespace FDT\doctrineExtensions\NestedSet;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\EventArgs;
use Doctrine\ODM\MongoDB\Events;
use \ReflectionClass;

class NestedSetListener implements EventSubscriber
{
        
    /**
     * Specifies the list of events to listen
     * 
     * @return array
     */
    public function getSubscribedEvents()
    {
        
        
        return array
				    (
				        Events::prePersist,
				        Events::preUpdate
				    );
    }
    
    private function implementsInterface ($document)
    {
        
        $rc1 = new ReflectionClass($document);
        
        return $rc1->implementsInterface ('FDT\doctrineExtensions\NestedSet\Documents\BaseNode');
    
    
    }
    
    public function prePersist(EventArgs $args)
    {
        $document = $args->getDocument();
                                
        if ($this->implementsInterface ($document))
        {
        	$document->setLevel ();
        
        }
                
        
        
    }
    
    public function preUpdate(EventArgs $args)
    {
        $document = $args->getDocument();
        
        if ($this->implementsInterface ($document))
        {
        	$document->setLevel ();
        
        	$dm = $args->getDocumentManager();
        	$class = $dm->getClassMetadata(get_class($document));
        	$dm->getUnitOfWork()->recomputeSingleDocumentChangeSet($class, $document);
       
        }
        
    }
    
    
    
        




}