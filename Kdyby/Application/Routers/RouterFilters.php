<?php

namespace Kdyby\Application\Routers;

use Doctrine;
use Nette;
use Kdyby;



class RouterFilters extends Nette\Object
{

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;



	/**
	 * @param Doctrine\ORM\EntityManager $em
	 */
	public function __construct(Doctrine\ORM\EntityManager $em)
	{
		$this->entityManager = $em;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

}