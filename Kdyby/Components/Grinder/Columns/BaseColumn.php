<?php

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
	 * @return mixed
	 */
	public function getValue()
	{
		$data = $this->getGrid()->getCurrentRecord();

		if ($this->value) {
			return $this->value;

		} elseif (is_object($data)) {
			if (isset($data->{$this->name})) {
				return $data->{$this->name};
			}

			return $data->{'get' . ucfirst($this->name)}();

		} elseif (is_array($data)) {
			return $data[$this->name];
		}

		return NULL;
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
	 * @return void
	 */
	public function render()
	{
		echo call_user_func(array($this->renderer, 'renderCell'), $this);
	}

}