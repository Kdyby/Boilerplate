<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder;



/**
 * Data model
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
interface IModel extends \IteratorAggregate, \Countable
{
	const ASC = "asc";
	const DESC = "desc";

	/**
	 * @return Filters\IFragmentsBuilder
	 */
	public function createFragmentsBuilder();

	/**
	 * @param Filters\FiltersMap $filters
	 */
	public function applyFilters(Filters\FiltersMap $filters);

	/**
	 * @param array|object $item
	 * @return scalar
	 */
	public function getUniqueId($item);

	/**
	 * @param scalar $uniqueId
	 * @return array|object
	 */
	public function getItemByUniqueId($uniqueId);

	/**
	 * @param array $uniqueIds
	 * @return array
	 */
	public function getItemsByUniqueIds(array $uniqueIds);

	/**
	 * @return array
	 */
	public function getItems();

	/**
	 * @param string $column
	 * @param string $type
	 */
	public function applySorting($column, $type);

	/**
	 * @param int $limit
	 */
	public function setLimit($limit);

	/**
	 * @param int $limit
	 */
	public function setOffset($offset);

}