<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters\Fragments;

use Kdyby;
use Kdyby\Components\Grinder\Filters;
use Kdyby\Components\Grinder\Filters\Filter;
use Nette;



/**
 * @author Filip Procházka
 */
class DibiFluentBuilder extends Nette\Object implements Filters\IFragmentsBuilder
{

	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildEquals($value, Filter $filter)
	{
		if ($value === NULL) {
			return NULL;
		}

		if (is_array($value)) {
			return array('%sql IN %l', $filter->column, $value);
		}

		return array('%sql = %' . $filter->getSqlType(), $filter->column, $value);
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildLike($value, Filter $filter)
	{
		return $value !== NULL ? array('%sql LIKE %s', $filter->column, '%' . $value . '%') : NULL;
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildHigherOrEqualThan($value, Filter $filter)
	{
		return $value !== NULL ? array('%sql >= %' . $filter->getSqlType(), $filter->column, $value) : NULL;
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildHigherThan($value, Filter $filter)
	{
		return $value !== NULL ? array('%sql > %' . $filter->getSqlType(), $filter->column, $value) : NULL;
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildLowerOrEqualThan($value, Filter $filter)
	{
		return $value !== NULL ? array('%sql <= %' . $filter->getSqlType(), $filter->column, $value) : NULL;
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildLowerThan($value, Filter $filter)
	{
		return $value !== NULL ? array('%sql < %' . $filter->getSqlType(), $filter->column, $value) : NULL;
	}



	/**
	 * @param string|array $value
	 * @param Filter $filter
	 * @return array
	 */
	public function buildNull($value, Filter $filter)
	{
		if ($value === NULL) {
			return NULL;
		}

		return array($value ? '%sql IS NULL' : '%sql IS NOT NULL', $filter->column);
	}

}