<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use DoctrineExtensions\Paginate\Paginate;
use Kdyby;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka
 */
abstract class QueryObjectBase implements IQueryObject
{

	/** @var Paginator */
	private $paginator;

	/** @var Doctrine\ORM\Query */
	private $query;



	/**
	 * @param Paginator $paginator
	 */
	public function __construct(Paginator $paginator = NULL)
	{
		$this->paginator = $paginator;
	}



	/**
	 * @return Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}



	/**
	 * @param EntityRepository $repository
	 * @return Doctrine\ORM\Query|Doctrine\ORM\QueryBuilder
	 */
	protected abstract function doCreateQuery(EntityRepository $repository);



	/**
	 * @return Doctrine\ORM\Query
	 */
	protected function getQuery()
	{
		if ($this->query === NULL) {
			$this->query = $this->doCreateQuery($repository);
			if ($this->query instanceof Doctrine\ORM\QueryBuilder) {
				$this->query = $this->query->getQuery();
			}
		}

		return $this->query;
	}



	/**
	 * @param EntityRepository $repository
	 * @return integer
	 */
	public function count(EntityRepository $repository)
	{
		return Paginate::getTotalQueryResults($this->getQuery());
	}



	/**
	 * @param EntityRepository $repository
	 * @return array
	 */
	public function fetch(EntityRepository $repository)
	{
		if ($this->paginator) {
			$query = Paginate::getPaginateQuery($this->getQuery(), $this->paginator->getOffset(), $this->paginator->getLength()); // Step 2 and 3

		} else {
			$query = $this->getQuery()->setMaxResults(NULL)->setFirstResult(NULL);
		}

		return $query->getResult();
	}



	/**
	 * @param EntityRepository $repository
	 * @return object
	 */
	public function fetchOne(EntityRepository $repository)
	{
		try {
			$query = $this->getQuery()->setFirstResult(NULL)->setMaxResults(1);
			return $query->getSingleResult();

		} catch (NoResultException $e) {
			return NULL;

		} catch (NonUniqueResultException $e) {
			return NULL;
		}
	}

}