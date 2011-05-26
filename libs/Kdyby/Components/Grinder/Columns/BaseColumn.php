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

	/** @var bool */
	protected $sortable = FALSE;

	/** @var string|callable */
	private $cellHtmlClass;



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if ($this->value) {
			return $this->value;
		}

		return $this->getGrid()->getRecordProperty($this->name);
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
	 * @return Nette\Utils\Html
	 */
	abstract function getControl();



	/**
	 * @return void
	 */
	public function render()
	{
		echo $this->__toString();
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return call_user_func(array($this->renderer, 'renderCell'), $this);
	}

}