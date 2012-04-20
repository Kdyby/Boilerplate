<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\ORM\AbstractQuery;
use Kdyby;
use Kdyby\Persistence\IQueryable;
use Nette;
use Nette\Utils\Paginator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class QueryObjectBase extends Nette\Object implements Kdyby\Persistence\IQueryObject
{

	/**
	 * @var \Doctrine\ORM\Query
	 */
	private $lastQuery;

	/**
	 * @var \Kdyby\Doctrine\ResultSet
	 */
	private $lastResult;



	/**
	 */
	public function __construct()
	{

	}



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 * @return \Doctrine\ORM\Query|\Doctrine\ORM\QueryBuilder
	 */
	protected abstract function doCreateQuery(Kdyby\Persistence\IQueryable $repository);



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 *
	 * @throws \Kdyby\UnexpectedValueException
	 * @return \Doctrine\ORM\Query
	 */
	private function getQuery(IQueryable $repository)
	{
		$query = $this->doCreateQuery($repository);
		if ($query instanceof Doctrine\ORM\QueryBuilder) {
			$query = $query->getQuery();
		}

		if (!$query instanceof Doctrine\ORM\Query) {
			$class = $this->getReflection()->getMethod('doCreateQuery')->getDeclaringClass();
			throw new Kdyby\UnexpectedValueException("Method " . $class . "::doCreateQuery() must return" .
				" instanceof Doctrine\\ORM\\Query or instanceof Doctrine\\ORM\\QueryBuilder, " .
				Kdyby\Tools\Mixed::getType($query) . " given.");
		}

		if ($this->lastQuery && $this->lastQuery->getDQL() === $query->getDQL()) {
			$query = $this->lastQuery;
		}

		if ($this->lastQuery !== $query) {
			$this->lastResult = new ResultSet($query);
		}

		return $this->lastQuery = $query;
	}



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 *
	 * @return integer
	 */
	public function count(IQueryable $repository)
	{
		return $this->fetch($repository)
			->getTotalCount();
	}



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 * @param int $hydrationMode
	 *
	 * @return \Kdyby\Doctrine\ResultSet|array
	 */
	public function fetch(IQueryable $repository, $hydrationMode = AbstractQuery::HYDRATE_OBJECT)
	{
		$query = $this->getQuery($repository)
			->setFirstResult(NULL)
			->setMaxResults(NULL);

		return $hydrationMode !== AbstractQuery::HYDRATE_OBJECT
			? $query->execute($hydrationMode)
			: $this->lastResult;
	}



	/**
	 * @param \Kdyby\Persistence\IQueryable $repository
	 * @return object
	 */
	public function fetchOne(IQueryable $repository)
	{
		$query = $this->getQuery($repository)
			->setFirstResult(NULL)
			->setMaxResults(1);

		return $query->getSingleResult();
	}



	/**
	 * @internal For Debugging purposes only!
	 * @return \Doctrine\ORM\Query
	 */
	public function getLastQuery()
	{
		return $this->lastQuery;
	}

}
