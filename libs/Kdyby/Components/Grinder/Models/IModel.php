<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Models;

use Kdyby\Components\Grinder\Filters;




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

	public function createFragmentsBuilder();

	public function applyFilters(Filters\FiltersMap $filters);

	public function getUniqueId($item);

	public function getItemByUniqueId($uniqueId);

	public function getItemsByUniqueIds(array $uniqueIds);

	public function getItems();

	public function setSorting($column, $type);

	public function setLimit($limit);

	public function setOffset($offset);

}