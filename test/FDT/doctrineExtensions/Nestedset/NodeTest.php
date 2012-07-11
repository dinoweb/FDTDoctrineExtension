<?php

namespace FDT\doctrineExtensions\NestedSet;

use Doctrine\Common\Util\Debug;
use Nestedset\Documents\Categoria;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

use Doctrine\MongoDB\Connection;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;


use PHPUnit_Framework_TestCase;
use Mongo;

class NestedsetTest extends \PHPUnit_Framework_TestCase
{
    private $documentManager;

    public function setUp()
    {
        $config = new \Doctrine\ODM\MongoDB\Configuration();
        $config->setProxyDir(__DIR__ . '/Proxy');
        $config->setProxyNamespace('dinoWeb\Nestedset\Proxy');
        $config->setHydratorDir(__DIR__ . '/Hydrator');
        $config->setHydratorNamespace('dinoWeb\Nestedset\Hydrator');
        $config->setDefaultDB('dinoWeb_Nestedset_test');
        $config->setAutoGenerateProxyClasses(true);

        $this->documentManager = $this->getTestDocumentManager ($config);



    }

    public function testNodeWrapperCreation ()
    {

        $mobili = new Categoria ();
        $mobili->setName ('Mobili');
        $mobili->setEbayId (200);
        $mobili->getEbayOfferedEnable (1);

        $nodeRoot = $this->getTreeManager ()->getNode ($mobili);

        $this->assertEquals ('FDT\doctrineExtensions\NestedSet\NodeWrapper', get_class($nodeRoot));


    }

    public function testAddChild ()
    {

        $categoria1 = new Categoria ();
        $categoria1->setName ('Mobili');
        $categoria1->setEbayId (200);
        $categoria1->getEbayOfferedEnable (1);

        $categoria2 = new Categoria ();
        $categoria2->setName ('Sedie');
        $categoria2->setEbayId (300);
        $categoria2->getEbayOfferedEnable (1);

        $nodeRoot = $this->getTreeManager ()->getNode ($categoria1);

        $nodeRoot->addChild ($categoria2);

        $this->documentManager->persist($categoria2);
        $this->documentManager->flush ();

        $this->assertEquals (1, $categoria2->getLevel());

        $this->assertEquals (1, count ($categoria2->getAncestors()));


        $descendants = $nodeRoot->getDescendants ();

        $this->assertEquals (1, $descendants->count());

        $categoria3 = new Categoria ();
        $categoria3->setName ('Sgabelli');
        $categoria3->setEbayId (400);
        $categoria3->getEbayOfferedEnable (1);

        $nodeRoot->addChild ($categoria3);

        $this->documentManager->persist($categoria3);
        $this->documentManager->flush ();

        $this->assertEquals (1, $categoria3->getLevel());

        $descendants = $nodeRoot->getDescendants ();

        $this->assertEquals (2, $descendants->count());

        $categoria4 = new Categoria ();
        $categoria4->setName ('Sgabelli belli');
        $categoria4->setEbayId (500);
        $categoria4->getEbayOfferedEnable (1);

        $nodeCategoria3 = $this->getTreeManager ()->getNode ($categoria3);
        $nodeCategoria3->addChild ($categoria4);

        $this->documentManager->persist($categoria4);
        $this->documentManager->flush ();

        $this->assertEquals (2, $categoria4->getLevel());


    }

    public function testNodeWrapper ()
    {

        $mobili = $this->getCategoria('Mobili');
        $sedie = $this->getCategoria('Sedie');

        $nodeRoot = $this->getTreeManager ()->getNode ($mobili);

        $nodeRoot->addChild ($sedie);

        $this->documentManager->persist($sedie);
        $this->documentManager->flush ();

        $sgabelli = $this->getCategoria('Sgabelli');

        $nodeRoot->addChild ($sgabelli);

        $this->documentManager->persist($sgabelli);
        $this->documentManager->flush ();

        $sgabelliAlti = $this->getCategoria('Sgabelli Alti');

        $nodeSgabelli = $this->getTreeManager ()->getNode ($sgabelli);

        $nodeSgabelli->addChild ($sgabelliAlti);

        $this->documentManager->persist($sgabelliAlti);
        $this->documentManager->flush ();

        $this->assertEquals (1, $nodeSgabelli->getLevel());

        $nodeSgabelliAlti = $this->getTreeManager ()->getNode ($sgabelliAlti);

        $this->assertEquals (2, $nodeSgabelliAlti->getLevel());

        $arrayTree =  ($this->getTreeManager ()->getTreeAsArray ($mobili));

        $this->assertEquals (4, count ($arrayTree));


        $nodeSgabelliAlti = $this->getTreeManager ()->getNode ($sgabelliAlti);
        $pathSgabelliAlti = $nodeSgabelliAlti->getPath();

        $this->assertEquals ('Mobili_Sgabelli_Sgabelli Alti', $pathSgabelliAlti);

        $pathRoot = $nodeRoot->getPath();

        $this->assertEquals ('Mobili', $pathRoot);

        $arrayTree =  ($this->getTreeManager ()->getTreeAsArray ($mobili, TRUE, 3));


    }


    private function getCategoria ($nome)
    {

        $categoria = new Categoria ();
        $categoria->setName ($nome);
        $categoria->setEbayId (200);
        $categoria->getEbayOfferedEnable (1);

        return $categoria;


    }


    private function getTestDocumentManager($configuration)
    {

        $reader = new AnnotationReader();
        $annotationDriver = new AnnotationDriver($reader, __DIR__ . '/../Documents');
        $configuration->setMetadataDriverImpl($annotationDriver);

        $mongoClass = new Mongo('int-app-glusterfs.gemmyx.com:8080', array('persist'=>'x'));

        $eventManager = new EventManager();
            $eventManager->addEventSubscriber(new NestedSetListener());

            return DocumentManager::create(new Connection($mongoClass), $configuration, $eventManager);

    }

    private function getTreeManager ()
    {

        return new TreeManager ($this->documentManager);

    }

    private function getTree ()
    {




    }

}