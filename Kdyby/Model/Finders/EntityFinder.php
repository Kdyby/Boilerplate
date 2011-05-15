<?php

namespace Kdyby\Model\Finders;

use Doctrine;
use Doctrine\ORM\QueryBuilder;
use DoctrineExtensions\Paginate\Paginate;
use Nette;
use Kdyby;
use Kdyby\Components\Grinder;
use Kdyby\Model\BaseModel;



class EntityFinder extends Nette\Object implements \IteratorAggregate, \Countable
{
	const STATE_DIRTY = 0;
	const STATE_CLEAN = 1;

	/** @var QueryBuilder */
	private $query;

	/** @var QueryBuilder */
	private $qbPrototype;

	/** @var array */
	private $queryFilterParts = array();

	/** @var int */
	private $state = 0;

	/** @var BaseModel */
	private $model;

	/** @var array of Filters\IFinderFilter */
	private $filters = array();



	/**
	 * @param QueryBuilder $qb
	 * @param BaseModel $model
	 */
	public function __construct(QueryBuilder $qb, BaseModel $model)
	{
		$this->qbPrototype = $qb;
		$this->model = $model;
	}



	/**
	 * @return BaseModel
	 */
	public function getModel()
	{
		return $this->model;
	}



	/**
	 * @param int $state
	 */
	public function setState($state)
	{
		$this->state = (bool)$state;
	}



	/**
	 * @return int
	 */
	public function getState()
	{
		return (int)$this->state();
	}



	/**
	 * @return QueryBuilder
	 */
	public function getQueryBuilder()
	{
		$qb = $this->qbPrototype;
		$this->setState(self::STATE_DIRTY);

		return $qb;
	}



	/**
	 * @return QueryBuilder
	 */
	public function getFilteredQueryBuilder()
	{
		if ($this->getState() !== self::STATE_DIRTY && $this->query->getDQL() !== $this->qbPrototype->getDQL()) {
			// TODO: betted condition?
			$this->setState(self::STATE_DIRTY);
		}

		if ($this->getState() === self::STATE_DIRTY) {
			$this->query = $this->doApplyFilters(clone $this->qbPrototype);
			$this->setState(self::STATE_CLEAN);
		}

		return $this->query;
	}



	/**
	 * @param QueryBuilder $query
	 * @return QueryBuilder
	 */
	private function doApplyFilters(QueryBuilder $query)
	{
		foreach ($this->getQueryFilterParts() as $queryPart) {
			$query->add('where', $queryPart, TRUE);
		}

		return $query;
	}



	/**
	 * @param string $name
	 * @param array $fields
	 * @param string $methodName
	 * @param bool $independent
	 * @return Filters\ResultFilter
	 */
	public function addResultFilter($name, array $fields, $methodName, $independent = FALSE)
	{
		if (isset($this->filters[$name])) {
			throw new Nette\InvalidStateException(get_class($this->filters[$name]) . " with name " . $name . " is already defined.");
		}

		$this->filters[$name] = $filter = new Filters\ResultFilter($name, $this->getMethod($methodName), $fields);
		$filter->setIndependent($independent);

		return $filter;
	}



	/**
	 * @param string $name
	 * @param bool $independent
	 * @return Filters\FilterGroup
	 */
	public function addResultFilterGroup($name, $independent = FALSE)
	{
		if (isset($this->filters[$name])) {
			throw new Nette\InvalidStateException(get_class($this->filters[$name]) . " with name " . $name . " is already defined.");
		}

		$this->filters[$name] = $group = new Filters\FilterGroup($name);
		$group->setIndependent($independent);

		return $group;
	}



	/**
	 * @param string $name
	 * @return array of Kdyby\Model\Finders\Filters\IFinderFilter
	 */
	public function getFilters()
	{
		return $this->filters;
	}



	/**
	 * @param array $state
	 */
	public function loadFiltersState($state)
	{
		foreach ($this->getFilters() as $name => $filter) {
			$filter->loadState(isset($state[$name]) ? $state[$name] : array());
		}
	}



	/**
	 * @return array
	 */
	public function saveFiltersState()
	{
		$state = array();

		foreach ($this->getFilters() as $name => $filter) {
			$state[$name] = $filter->saveState();
		}

		return $state;
	}



	/**
	 * @param array $state
	 * @param string $filterName
	 */
	public function handleChangeFiltersState(array $newState, $filterName)
	{
		if (!isset($this->filters[$filterName])) {
			throw new Nette\InvalidStateException("Any Kdyby\Model\Finders\Filters\IFinderFilter named " . $filterName . " is not defined.");
		}

		if ($this->filters[$filterName]->handleChangeState($newState)) {
			foreach ($this->filters as $name => $filter) {
				if ($name === $filterName) {
					continue;
				}

				$filter->handleChangeState(array());
			}
		}
	}



	/**
	 * @return array of Doctrine\ORM\Query\Expr
	 */
	private function getQueryFilterParts()
	{
		if (count($this->queryFilterParts) === count($this->filters)) {
			return array_filter($this->queryFilterParts);
		}

		$this->queryFilterParts = array();
		foreach ($this->filters as $filter) {
			$this->queryFilterParts[] = $filter->buildFragment();
		}
		return $this->queryFilterParts;
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return $this->getQuery()->select("count(" . $this->getQuery()->getRootAlias() . ") fullcount")->getQuery()->getSingleScalarResult();
	}



	/**
	 * @return Doctrine\ORM\PersistentCollection
	 */
	public function getIterator()
	{
		return $this->getFilteredQueryBuilder()->getQuery()->getResult();
	}



	/**
	 * Get Gridito model
	 * @return Grinder\Models\IModel
	 */
	public function getGrinderModel()
	{
		$em = $this->getModel()->getEntityManager();
		$identifier = $em->getClassMetadata($this->entityName)->getSingleIdentifierFieldName();

		$grinderModel = new Grinder\DoctrineQueryBuilderModel($this->getFilteredQueryBuilder());
		$grinderModel->setPrimaryKey($identifier);

		return $grinderModel;
	}

}