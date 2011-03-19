<?php

namespace Gridito;

/**
 * Grid column
 *
 * @author Jan Marek
 * @license MIT
 */
class Column extends \Nette\Application\Control
{
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var string */
	private $label;

	/** @var callback */
	private $renderer = null;

	/** @var bool */
	private $sortable = false;

	/** @var string */
	private $dateTimeFormat = "j.n.Y G:i";

	/** @var string|callable */
	private $cellClass = null;

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getters & setters">

	public function setCellClass($class)
	{
	    $this->cellClass = $class;
		return $this;
	}



	public function getCellClass($iterator, $row)
	{
		if (is_callable($this->cellClass)) {
			return call_user_func($this->cellClass, $iterator, $row);
		} elseif (is_string($this->cellClass)) {
			return $this->cellClass;
		} else {
			return null;
		}
	}



	/**
	 * Get label
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}



	/**
	 * Set label
	 * @param string label
	 * @return Column
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}



	/**
	 * Get cell renderer
	 * @return callback
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * Set cell renderer
	 * @param callback cell renderer
	 * @return Column
	 */
	public function setRenderer($cellRenderer)
	{
		$this->renderer = $cellRenderer;
		return $this;
	}



	/**
	 * Is sortable?
	 * @return bool
	 */
	public function isSortable() {
		return $this->sortable;
	}



	/**
	 * Set sortable
	 * @param bool sortable
	 * @return Column
	 */
	public function setSortable($sortable) {
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
		} else {
			return null;
		}
	}



	/**
	 * Get date/time format
	 * @return string
	 */
	public function getDateTimeFormat() {
		return $this->dateTimeFormat;
	}



	/**
	 * Set date/time format
	 * @param string datetime format
	 * @return Column
	 */
	public function setDateTimeFormat($dateTimeFormat) {
		$this->dateTimeFormat = $dateTimeFormat;
		return $this;
	}



	/**
	 * Get grid
	 * @return Grid
	 */
	public function getGrid() {
		return $this->getParent()->getParent();
	}

	// </editor-fold>

	/**
	 * Render boolean
	 * @param bool value
	 */
	public static function renderBoolean($value)
	{
		$icon = $value ? "check" : "closethick";
		echo '<span class="ui-icon ui-icon-' . $icon . '"></span>';
	}



	/**
	 * Render datetime
	 * @param Datetime value
	 * @param string datetime format
	 */
	public static function renderDateTime($value, $format)
	{
		echo $value->format($format);
	}



	/**
	 * Default cell renderer
	 * @param mixed $record
	 * @param Column $column
	 */
	public function defaultCellRenderer($record, $column) {
		$name = $column->getName();
		$value = $record->$name;

		// boolean
		if (is_bool($value)) {
			self::renderBoolean($value);

		// date
		} elseif ($value instanceof \DateTime) {
			self::renderDateTime($value, $this->dateTimeFormat);

		// other
		} else {
			echo $value;
		}
	}



	/**
	 * Render cell
	 * @param mixed record
	 */
	public function renderCell($record) {
		call_user_func($this->renderer ?: array($this, "defaultCellRenderer"), $record, $this);
	}

}