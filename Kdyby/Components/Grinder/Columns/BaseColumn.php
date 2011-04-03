<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Kdyby;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;



/**
 * Grid column
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
abstract class BaseColumn extends Nette\Application\PresenterComponent
{
	/** @var string */
	private $caption;

	/** @var mixed */
	private $value;

	/** @var IGridRenderer */
	private $renderer = NULL;

	/** @var bool */
	protected $sortable = FALSE;

	/** @var string|callable */
	private $cellHtmlClass = NULL;



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
	 * @param object $row
	 * @return string
	 */
	public function getCellHtmlClass(\Iterator $iterator, $record)
	{
		if (is_callable($this->cellHtmlClass)) {
			return call_user_func($this->cellHtmlClass, $iterator, $record);

		} elseif (is_string($this->cellHtmlClass)) {
			return $this->cellHtmlClass;
		}

		return null;
	}



	/**
	 * @return string
	 */
	public function getCaption()
	{
		return $this->caption;
	}



	/**
	 * @param string
	 * @return Column
	 */
	public function setCaption($caption)
	{
		$this->caption = $caption;
		return $this;
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
	 * Get cell renderer
	 * @return IGridRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * Set cell renderer
	 * @param IGridRenderer cell renderer
	 * @return Column
	 */
	public function setRenderer(IGridRenderer $cellRenderer)
	{
		$this->renderer = $cellRenderer;
		return $this;
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
	 * Get grid
	 * @return Grid
	 */
	public function getGrid()
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid');
	}



	/**
	 * @return void
	 */
	public function render()
	{
		echo call_user_func(array($this->renderer, 'renderCell'), $this);
	}

}