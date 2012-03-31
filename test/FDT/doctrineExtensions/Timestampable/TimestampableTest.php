<?php
namespace Timestampable\Documents;

use Doctrine\Common\Util\Debug;
use Timestampable\Documents\Article;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

use Doctrine\MongoDB\Connection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;

use FDT\doctrineExtensions\Timestampable\TimestampableListener;


use PHPUnit_Framework_TestCase;
use Mongo;

class TimestampableTest extends \PHPUnit_Framework_TestCase
{
    private $documentManager;

    public function setUp()
    {        
        $config = new \Doctrine\ODM\MongoDB\Configuration();
        $config->setProxyDir(__DIR__ . '/Proxy');
        $config->setProxyNamespace('FDT\doctrineExtensions\Timestampable\Proxy');
        $config->setHydratorDir(__DIR__ . '/Hydrator');
        $config->setHydratorNamespace('Hydrator');
        $config->setDefaultDB('dinoWeb_Timestampable_test');
                
        $this->documentManager = $this->getTestDocumentManager ();
                
        
    }
    
    
    public function testTheTree()
    {
       	$articolo = new Article ();
       	
       	$articolo->setTitle ('Palla');
       	
       	$this->documentManager->persist ($articolo);
       	$this->documentManager->flush ();
    	    
    	print_r($articolo);
    
    }
    
    
    
    private function getTestDocumentManager()
    {
        $configuration = new Configuration();
        $configuration->setHydratorDir(__DIR__);
        $configuration->setHydratorNamespace('TestHydrator');
        $configuration->setProxyDir(__DIR__);
        $configuration->setProxyNamespace('TestProxy');

        $reader = new AnnotationReader();
        $reader->setDefaultAnnotationNamespace('Doctrine\ODM\MongoDB\Mapping\\');
        $annotationDriver = new AnnotationDriver($reader, __DIR__ . '/../Documents');
        $configuration->setMetadataDriverImpl($annotationDriver);
                
        $mongoClass = new Mongo('int-app-glusterfs.gemmyx.com:8080', array('persist'=>'x'));
        
        $eventManager = new EventManager();
		$eventManager->addEventSubscriber(new TimestampableListener());
		
		return DocumentManager::create(new Connection($mongoClass), $configuration, $eventManager);

    }
    
}