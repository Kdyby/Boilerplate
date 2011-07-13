<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Models;

use DibiFluent;
use Kdyby\Components\Grinder\Filters;



/**
 * DibiFluent model
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
class DibiFluentModel extends AbstractModel implements Kdyby\Components\Grinder\IModel
{

	/** @var DibiFluent */
	protected $fluent;

	/** @var string */
	protected $rowClass;



	/**
	 * @param DibiFluent $fluent
	 * @param type $rowClass
	 */
	public function __construct(DibiFluent $fluent, $rowClass = "DibiRow")
	{
		$this->fluent = $fluent;
		$this->rowClass = $rowClass;
	}



	/**
	 * @param Filters\FiltersMap $filters
	 */
	public function applyFilters(Filters\FiltersMap $filters)
	{
		foreach ($filters as $filter) {
			$fragments = $filter->createFragments();
			if ($fragments) {
				call_user_func_array(array($this->fluent, 'where'), $fragments);
			}
		}
	}



	/**
	 * @return Filters\IFragmentsBuilder
	 */
	public function createFragmentsBuilder()
	{
		return new Filters\Fragments\DibiFluentBuilder();
	}



	/**
	 * @param object $item
	 * @return mixed
	 */
	public function getUniqueId($item)
	{
		$dot = strpos($this->getPrimaryKey(), '.');
		$idColumn = substr($this->getPrimaryKey(), ($dot ? $dot+1 : 0));
		return $item->$idColumn;
	}



	/**
	 * @param int $uniqueId
	 * @return \DibiRow
	 */
	public function getItemByUniqueId($uniqueId)
	{
		$fluent = clone $this->fluent;
		$fluent->where("%n = %i", $this->getPrimaryKey(), $uniqueId);
		return $fluent->execute()->setRowClass($this->rowClass)->fetch();
	}



	/**
	 * @param array $uniqueIds
	 * @return array
	 */
	public function getItemsByUniqueIds(array $uniqueIds)
	{
		$fluent = clone $this->fluent;
		$fluent->where("%n IN %l", $this->getPrimaryKey(), $uniqueIds);
		return $fluent->execute()->setRowClass($this->rowClass)->fetchAll();
	}



	/**
	 * @return array
	 */
	public function getItems()
	{
		$fluent = clone $this->fluent;

		$fluent->limit($this->getLimit());
		$fluent->offset($this->getOffset());

		list($sortColumn, $sortType) = $this->getSorting();
		if ($sortColumn) {
			$fluent->orderBy("[$sortColumn] $sortType");
		}

		return $fluent->execute()->setRowClass($this->rowClass)->fetchAll();
	}



	/**
	 * @return int
	 */
	protected function doCount()
	{
		return $this->fluent->count();
	}

}