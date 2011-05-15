<?php

namespace Kdyby\Components\Grinder\Models;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;



/**
 * Doctrine QueryBuilder model
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
class DoctrineQueryBuilderModel extends AbstractModel
{
	/** @var Doctrine\ORM\QueryBuilder */
	private $qb;



	/**
	 * Construct
	 * @param Doctrine\ORM\QueryBuilder query builder
	 */
	public function __construct(QueryBuilder $qb)
	{
		$this->qb = $qb;
	}



	/**
	 * @return int
	 */
	protected function doCount()
	{
		$qb = clone $this->qb;
		$qb->select('count(' . $qb->getRootAlias() . ') fullcount');
		return $qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
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