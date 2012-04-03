<?php

namespace FDT\doctrineExtensions\NestedSet;

use FDT\doctrineExtensions\NestedSet\Documents\BaseNode;

class NodeWrapper
{

	private $document;
	private $documentManager;

	public function __construct(BaseNode $document, \Doctrine\ODM\MongoDB\DocumentManager $documentManager)
	{

		$this->setDocument($document);
		$this->documentManager = $documentManager;

	}

	private function setDocument (BaseNode $document)
	{

	   $this->document = $document;

	}

	public function getDocument ()
	{

		return $this->document;

	}

	public function getDocumentManager ()
	{

		return $this->documentManager;

	}

	public function getAncestors ()
	{

		return $this->getDocument ()->getAncestors ();


	}

	public function addChild (BaseNode $childDocument, $print = false)
	{

		$oldChildren = $this->getChildrenByParent ($childDocument);
		$originalDocument = $this->getDocument();

		$childDocument->setParent ($this->getDocument ());
		$childDocument->clearAncestors();

		$ancestorsParent = $this->getAncestors ();


		if (count ($ancestorsParent) > 0)
		{
			foreach ($ancestorsParent as $ancestor)
			{
				$childDocument->addAncestor ($ancestor);
			}
		}
		$childDocument->addAncestor ($this->getDocument ());

		if ($oldChildren and $oldChildren->count () > 0)
		{
		  $this->setDocument ($childDocument);
		  foreach ($oldChildren as $child)
		  {

		      $this->addChild ($child);

		  }
		  $this->setDocument ($originalDocument);

		}


	}

	private function getDocumentClassName ()
	{

		return get_class($this->getDocument ());

	}

	public function hasChildren ()
	{

		$descendants = $this->getDescendants ();

		if ($descendants->count() > 0)
		{

			return TRUE;

		}

		return FALSE;

	}

	public function getChildren ()
	{

		return $this->getDescendants (1);

	}

	public function getChildrenByParent (BaseNode $childDocument = NULL)
	{
	   if (is_null($childDocument))
	   {
	       $id =  $this->getDocument()->getId();

	   }
	   else
	   {
	       $id = $childDocument->getId();
	   }

	   if ($id)
	   {
	       $qb =  $this->getDocumentManager()->createQueryBuilder($this->getDocumentClassName ())
			   ->field('parent.id')->equals($id);

            $query = $qb->getQuery();

		  $results = $query->execute();

		  return $results;

	   }

	   return false;



	}

	public function getDescendants ($level = 1)
	{

		$qb =  $this->getDocumentManager()->createQueryBuilder($this->getDocumentClassName ())
			   ->field('ancestors.id')->equals($this->getDocument()->getId())
			   ->sort ('index', 'asc')->sort('updated', 'desc');

        $levelOk = ($this->getDocument()->getLevel() + $level);

        if ($level != 'ALL')
        {
            $qb = $qb->field('level')->gte($this->getDocument()->getLevel());
            $qb = $qb->field('level')->lte($levelOk);
        }




		$query = $qb->getQuery();

		$results = $query->execute();

		return $results;



	}

	public function getLevel ()
	{


		return $this->getDocument()->getLevel();


	}

	public function getPath ($separator = '_')
	{
		$ancestors = $this->getDocument()->getAncestors ();

		$path = NULL;

		if (count ($ancestors) > 0)
		{

			foreach ($ancestors as $ancestor)
			{

				$path .= $ancestor->getStringForPath ();

				$path .= $separator;


			}

		}


		$path .= $this->getDocument()->getStringForPath ();

		return $path;


	}

	public function setAsRoot()
    {
        $oldParent = $this->getDocument()->getParent ();

        $this->getDocument()->removeParent();
        $this->getDocument()->clearAncestors();

        $descendants = $this->getDescendants('ALL');

        if (!is_null($oldParent) and $descendants->count () > 0)
        {
            foreach ($descendants as $descendant)
            {

                $descendant->removeAncestor ($oldParent);

            }


        }

        return $this->getDocument();


    }


}




?>