<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters\Fragments;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kdyby;
use Kdyby\Components\Grinder\Filters;
use Kdyby\Components\Grinder\Filters\Filter;
use Nette;



/**
 * @author Filip Procházka
 */
class DoctrineQueryBuilder extends Nette\Object implements Filters\IFragmentsBuilder
{

	/** @var Doctrine\ORM\Query\Expr */
	private $expr;

	/** @var QueryBuilder */
	private $qb;



	public function __construct()
	{
		$this->expr = new Expr();
	}



	/**
	 * @param QueryBuilder $qb
	 * @return DoctrineQueryBuilder
	 */
	public function setQueryBuilder(QueryBuilder $qb)
	{
		$this->qb = $qb;
		return $this;
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildEquals($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		$this->qb->setParameter($filter->getParameterName(), $value);

		if (is_array($value)) {
			return $this->expr->in($filter->column, ':' . $filter->getParameterName());
		}

		return $this->expr->eq($filter->column, ':' . $filter->getParameterName());
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildLike($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		$this->qb->setParameter($filter->getParameterName(), '%' . $value . '%');
		return $this->expr->like($filter->column, ':' . $filter->getParameterName());
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildHigherOrEqualThan($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		$this->qb->setParameter($filter->getParameterName(), $value);
		return $this->expr->gte($filter->column, ':' . $filter->getParameterName());
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildHigherThan($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		$this->qb->setParameter($filter->getParameterName(), $value);
		return $this->expr->gt($filter->column, ':' . $filter->getParameterName());
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildLowerOrEqualThan($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		$this->qb->setParameter($filter->getParameterName(), $value);
		return $this->expr->lte($filter->column, ':' . $filter->getParameterName());
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildLowerThan($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		$this->qb->setParameter($filter->getParameterName(), $value);
		return $this->expr->lt($filter->column, ':' . $filter->getParameterName());
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return Expr
	 */
	public function buildNull($value, Filter $filter)
	{
		if ($value === NULL) {
			return $filter->skipEmpty ? NULL : $filter->column . ' IS NULL';
		}

		if ($value) {
			return $filter->column . ' IS NULL';
		}

		return $filter->column . ' IS NOT NULL';
	}

}