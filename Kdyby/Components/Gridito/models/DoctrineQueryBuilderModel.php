<?php

namespace Gridito;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Doctrine QueryBuilder model
 *
 * @author Jan Marek
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



	protected function _count()
	{
		$qb = clone $this->qb;
		$qb->select('count(' . $qb->getRootAlias() . ') fullcount');
		return $qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);
	}



	public function getItems()
	{
		$this->qb->setMaxResults($this->getLimit());
		$this->qb->setFirstResult($this->getOffset());

		list($sortColumn, $sortType) = $this->getSorting();
		if ($sortColumn) {
			$this->qb->orderBy($this->qb->getRootAlias() . "." . $sortColumn, $sortType);
		}

		return $this->qb->getQuery()->getResult();
	}



	public function getItemByUniqueId($uniqueId)
	{
		$qb = clone $this->qb;
		return $qb->andWhere($this->qb->getRootAlias() . "." . $this->getPrimaryKey() . " = " . (int) $uniqueId)->getQuery()->getSingleResult();
	}
	
}