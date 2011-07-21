<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Columns;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;
use Nette\Utils\Html;



/**
 * Grid column
 *
 * @author Jan Marek
 * @author Filip Procházka
 *
 * @property-read string $realName
 * @property-read bool $sortable
 */
abstract class BaseColumn extends Nette\Application\UI\PresenterComponent
{

	/** @var string|Html */
	private $caption;

	/** @var mixed */
	private $value;

	/** @var Html */
	private $cellPrototype;

	/** @var Html */
	private $headingPrototype;

	/** @var string|callable */
	private $cellHtmlClass;



	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct();
		$this->monitor('Kdyby\Components\Grinder\Grid');
		$this->setCaption($caption);
		$this->cellPrototype = Html::el('td');
		$this->headingPrototype = Html::el('th');
	}



	/**
	 * @param boolean $need
	 * @return Grid
	 */
	public function getGrid($need = TRUE)
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid', $need);
	}



	/**
	 * @return string
	 */
	public function getRealName()
	{
		return $this->getGrid()->getComponentRealName($this);
	}



	/**
	 * @param string|Html caption
	 * @return BaseColumn
	 */
	public function setCaption($caption)
	{
		if (!is_string($caption) && !$caption instanceof Html && $caption !== NULL) {
			throw new Nette\InvalidArgumentException("Given caption must be either string or instance of Nette\\Web\\Html, " . gettype($caption) . " given.");
		}

		$this->caption = $caption;
		return $this;
	}



	/**
	 * @return string|Html
	 */
	public function getCaption()
	{
		return $this->caption;
	}



	/**
	 * @return string|Html
	 */
	public function getHeading()
	{
		$caption = $this->getCaption();
		if (!$caption) {
			return NULL;
		}

		return $caption;
	}



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if ($this->value) {
			return $this->value;
		}

		return $this->getGrid()->getRecordProperty($this->getRealName());
	}



	/**
	 * @param mixed $value
	 * @return BaseColumn
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}



	/**
	 * Is sortable?
	 * @return bool
	 */
	public function isSortable()
	{
		return FALSE;
	}



	/**
	 * @return NULL|string
	 */
	public function getSorting()
	{
		return NULL;
	}



	/**
	 * @param string|array|callable $class
	 * @return BaseColumn
	 */
	public function setCellHtmlClass($class)
	{
		if (is_array($class)) {
			$class = function (Nette\Iterators\CachingIterator $iterator, $record) use ($class) {
				if ($iterator->counter === 0) {
					return NULL;
				}

				$index = count($class) - ($this->counter % count($class));
				return $class[$index];
			};
		}

		if (!is_string($class) && !is_callable($class) && $class !== NULL) {
			throw new Nette\InvalidArgumentException("Given class must be either string, array or callback, " . gettype($caption) . " given.");
		}

	    $this->cellHtmlClass = $class;
		return $this;
	}



	/**
	 * @param \Iterator $iterator
	 * @return string|NULL
	 */
	public function getCellHtmlClass(\Iterator $iterator)
	{
		if (is_callable($this->cellHtmlClass)) {
			return call_user_func($this->cellHtmlClass, $iterator, $iterator->current());
		}

		return $this->cellHtmlClass;
	}



	/**
	 * @return Html
	 */
	public function getCellProtype()
	{
		return $this->cellPrototype;
	}



	/**
	 * @return Html
	 */
	public function getHeadingProtype()
	{
		return $this->headingPrototype;
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
		echo (string)$this->getControl();
	}



	/**
	 * @return void
	 */
	public function renderCell()
	{
		$cell = clone $this->cellPrototype;
		echo $cell->add($this->getControl());
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		try {
			return (string)$this->getControl();
		} catch (\Exception $e) {
			Nette\Diagnostics\Debugger::log($e);
		}
	}



	/**
	 * @return Nette\Application\UI\Link
	 */
	public function getSortingLink()
	{
		if ($this->getSorting() === NULL) {
			$args = array('sort!', $this->name, 'asc');

		} elseif($this->getSorting() === 'asc') {
			$args = array('sort!', $this->name, 'desc');

		} else {
			$args = array('sort!', NULL, NULL);
		}

		return callback($this->getGrid(), 'lazyLink')->invokeArgs($args);
	}



	/**
	 * @return Html
	 */
	public function getHeadingControl()
	{
		$heading = $this->getHeading();
		if (!$heading) {
			return NULL;
		}

		$span = Html::el('span')->class('grinder-sorting-' . ($this->getSorting() ?: 'no'));

		if ($this->isSortable()) {
			$span->add(
					Html::el('a')
						->href($this->getSortingLink())
						->{$heading instanceof Html ? 'add' : 'setText'}($heading)
				);

		} else {
			$span->{$heading instanceof Html ? 'add' : 'setText'}($heading);
		}

		return $span;
	}



	public function renderHeading()
	{
		echo $this->getHeadingControl();
	}



	public function renderHeadingCell()
	{
		$headingCell = clone $this->headingProtype;
		$control = $this->getHeadingControl();
		if ($control) {
			$headingCell->add($control);
		}

		echo $headingCell;
	}

}