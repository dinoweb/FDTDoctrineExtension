<?php

namespace FDT\doctrineExtensions\NestedSet;

use  FDT\doctrineExtensions\NestedSet\Documents\BaseNode;
use  Doctrine\ODM\MongoDB\DocumentManager;


class TreeManager
{

	private $documentManager;
	private $nodes = array ();

	public function __construct(DocumentManager $documentManager)
	{

		$this->documentManager = $documentManager;

	}

	public function getDocumentManager ()
	{
		return $this->documentManager;
	}



	public function getNode (BaseNode $document)
	{
		$objectId = spl_object_hash($document);

		if(!isset($this->nodes[$objectId]) || $this->nodes[$objectId]->getDocument() !== $document)
        {

        	$this->nodes[$objectId] =  new NodeWrapper ($document, $this->getDocumentManager ());


        }

    	return $this->nodes[$objectId];

	}

	public function getRoots ($document, $level = 0, $return = 'array')
	{

		$qb =  $this->getDocumentManager()->createQueryBuilder($document);
		$qb = $qb->field('level')->equals(0);

		$query = $qb->getQuery();

		$results = $query->execute();

		switch ($return)
		{
			case 'objects':
        		return $results;
        	break;

        	case 'array':

        		$arrayRoots = array();

        		foreach ($results as $rootDocument)
        		{

        			$arrayRoots[] = $this->getTreeAsArray ($rootDocument, $includeRoot = TRUE, $level);

        		}

        		return($arrayRoots);

        	break;


		}



	}


	public function getTreeAsArray (BaseNode $document, $includeRoot = TRUE, $level = 'ALL')
	{
		$arrayTree = array ();
		$collection = NULL;

		$node = $this->getNode ($document);
		$collection = 	$node->getDescendants ($level);

		if ($includeRoot)
		{
			$arrayTree[] = $document->getStringForPath();
		}

		if ($collection->count () > 0)
		{
			foreach ($collection as $document)
			{
				$arrayTree[] = $document->getStringForPath();
			}
		}

		return $arrayTree;




	}

	private function parentIsRoot ($parentId)
	{

	   $rootStringNumber =  preg_match('/^idRoot/', $parentId);

       if ($rootStringNumber > 0)
       {
         return true;
       }

       return false;

	}

	public function manageTreeMovements($document, array $requestData, $repository)
    {

        if ($this->parentIsRoot ($requestData['parentId']))
        {

            $nodeDocument = $this->getNode ($document);
            $nodeDocument->setAsRoot ();

        }
        else
        {

            $parentRecord = $repository->getByMyUniqueId ($requestData['parentId'], 'id');

            $parentNode = $this->getNode ($parentRecord);

            $parentNode->addChild ($document);
        }


            return $document;
    }



}

