<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Kdyby;



/**
 * Grid column
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
abstract class BaseColumn extends Kdyby\Components\Grinder\GridComponent
{
	/** @var mixed */
	private $value;

	/** @var array */
	private $filters = array();

	/** @var bool */
	protected $sortable = FALSE;

	/** @var string|callable */
	private $cellHtmlClass;



	/**
	 * @param callback $filter
	 * @return BaseColumn
	 */
	public function addFilter($filter)
	{
		$this->filters[] = callback($filter);
		return $this;
	}



	/**
	 * @return array
	 */
	public function getFilters()
	{
		return $this->filters;
	}



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		$record = $this->getGrid()->getCurrentRecord();
		$value = NULL;

		if ($this->value) {
			$value = $this->value;

		} elseif (is_object($record)) {
			if (isset($record->{$this->name})) {
				$value = $record->{$this->name};
			}

			$value = $record->{'get' . ucfirst($this->name)}();

		} elseif (is_array($record)) {
			$value = $record[$this->name];
		}

		foreach ($this->getFilters() as $filter) {
			$value = $filter($value, $record);
		}

		return $value;
	}



	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}



	/**
	 * Is sortable?
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->sortable;
	}



	/**
	 * Set sortable
	 * @param bool sortable
	 * @return Column
	 */
	public function setSortable($sortable)
	{
		$this->sortable = $sortable;
		return $this;
	}



	/**
	 * Get sorting
	 * @return string|null asc, desc or null
	 */
	public function getSorting()
	{
		$grid = $this->getGrid();
		if ($grid->sortColumn === $this->getName()) {
			return $grid->sortType;
		}

		return null;
	}



	/**
	 * @param string|callable $class
	 * @return BaseColumn
	 */
	public function setCellHtmlClass($class)
	{
	    $this->cellHtmlClass = $class;
		return $this;
	}



	/**
	 * @param \Iterator $iterator
	 * @return string
	 */
	public function getCellHtmlClass(\Iterator $iterator)
	{
		if (is_callable($this->cellHtmlClass)) {
			return call_user_func($this->cellHtmlClass, $iterator, $iterator->current());
		}

		return $this->cellHtmlClass;
	}



	/**
	 * @return void
	 */
	public function render()
	{
		echo call_user_func(array($this->renderer, 'renderCell'), $this);
	}

}