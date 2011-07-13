<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Models;

use Kdyby;
use Kdyby\Components\Grinder\Filters;
use Nette;
use Doctrine;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;



/**
 * Doctrine QueryBuilder model
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
class DoctrineQueryBuilderModel extends AbstractModel implements Kdyby\Components\Grinder\IModel
{

	/** @var QueryBuilder */
	private $qb;



	/**
	 * Construct
	 * @param QueryBuilder query builder
	 */
	public function __construct(QueryBuilder $qb)
	{
		$this->qb = $qb;
	}



	/**
	 * @param Filters\FiltersMap $filters
	 */
	public function applyFilters(Filters\FiltersMap $filters)
	{
		// for safe parameters
		$filters->getFragmentsBuilder()->setQueryBuilder($this->qb);

		foreach ($filters as $filter) {
			$method = $filter->createFragments();
			if ($method) {
				if ($method instanceof Expr\Select) {
					$this->qb->add('select', $method, TRUE);

				} elseif ($method instanceof Expr\Join) {
					$this->qb->add('join', $method, TRUE);

				} else {
					$this->qb->andWhere($method);
				}
			}
		}
	}



	/**
	 * @return Filters\IFragmentsBuilder
	 */
	public function createFragmentsBuilder()
	{
		return new Filters\Fragments\DoctrineQueryBuilder();
	}



	/**
	 * @return int
	 */
	protected function doCount()
	{
		try {
			$qb = clone $this->qb;
			return $qb->select('count(' . $qb->getRootAlias() . ') fullcount')
				->getQuery()
				->getSingleScalarResult();

		} catch (Doctrine\ORM\ORMException $e) {
			throw new Kdyby\Doctrine\ORM\QueryException($e->getMessage(), $this->qb->getQuery(), $e);
		}
	}



	/**
	 * @return array
	 */
	public function getItems()
	{
		$this->qb->setMaxResults($this->getLimit());
		$this->qb->setFirstResult($this->getOffset());

		list($sortColumn, $sortType) = $this->getSorting();
		if ($sortColumn) {
			$this->qb->orderBy($this->qb->getRootAlias() . '.' . $sortColumn, $sortType === self::DESC ? self::DESC : self::ASC);
		}

		return $this->qb->getQuery()->getResult();
	}



	/**
	 * @param scalar $uniqueId
	 * @return object|null
	 */
	public function getItemByUniqueId($uniqueId)
	{
		$qb = clone $this->qb;
		return $qb->andWhere($this->qb->getRootAlias() . '.' . $this->getPrimaryKey() . " = :grinderPrimaryKeyId")
			->setParameter('grinderPrimaryKeyId', $uniqueId)
			->getQuery()->getSingleResult();
	}



	/**
	 * @param array $uniqueIds
	 * @return array
	 */
	public function getItemsByUniqueIds(array $uniqueIds)
	{
		$qb = clone $this->qb;
		return $qb->andWhere($qb->expr()->in($this->qb->getRootAlias() . '.' . $this->getPrimaryKey(), $uniqueIds))
			->getQuery()->getResult();
	}

}