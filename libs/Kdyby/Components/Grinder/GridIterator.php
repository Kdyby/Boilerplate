<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class GridIterator extends \IteratorIterator
{

	/** @var Grid */
	private $grid;

	/** @var IModel */
	private $model;



	/**
	 * @param Grid $grid
	 * @param IModel $model
	 */
	public function __construct(Grid $grid, IModel $model)
	{
		$this->grid = $grid;
		$this->model = $model;

		// filter
		$this->model->applyFilters($this->grid->getFilters()->getFiltersMap());

		// count pages
		$this->grid->getPaginator()->setItemCount($this->model->count());

		// set limit & offset
		$this->model->setLimit($this->grid->getPaginator()->getLength());
		$this->model->setOffset($this->grid->getPaginator()->getOffset());

		// sorting
		$column = $this->grid->getColumn($this->grid->sortColumn, FALSE);
		if ($column && $column->isSortable()) {
			$this->model->applySorting($column->getRealName(), $this->grid->sortType);
		}

		// read items
		parent::__construct(new \ArrayIterator($this->model->getItems()), NULL);

		// items ids to form
		$ids = array_fill(0, $this->grid->itemsPerPage, NULL);
		foreach ($this->getItems() as $i => $item) {
			$ids[$i] = $this->model->getUniqueId($item);
		}

		$this->grid->getForm()->getComponent('ids')->setDefaults($ids);
	}



	/**
	 * @return array
	 */
	public function getItems()
	{
		return array_values(iterator_to_array($this->getInnerIterator()));
	}



	/**
	 * @var int
	 */
	public function getTotalCount()
	{
		return $this->grid->getPaginator()->getItemCount();
	}



	/**
	 * Return the current element
	 *
	 * @return mixed Can return any type.
	 */
	public function current()
	{
		$record = parent::current();
		$this->grid->bindRecord($this->key(), $record);
		return $record;
	}



	/**
	 * @return int|string
	 */
	public function getCurrentUniqueId()
	{
		return $this->grid->getModel()->getUniqueId($this->current());
	}

}