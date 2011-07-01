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
use Nette\Application\UI\Link;
use Nette\Templating\DefaultHelpers;
use Nette\Utils\Html;



/**
 * Grid column
 *
 * @author Filip Procházka
 * @license MIT
 */
class Column extends BaseColumn
{

	/** @var string */
	public $dateTimeFormat = "j.n.Y G:i";

	/** @var array */
	private $filters = array();

	/** @var bool */
	protected $sortable = TRUE;

	/** @var string|callback */
	private $link;

	/** @var array */
	private $mask = array();



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
		$value = parent::getValue();

		foreach ($this->getFilters() as $filter) {
			$value = $filter($value, $this->getGrid()->getCurrentRecord());
		}

		return $value;
	}



	/**
	 * When given closure|callback, it will receive arguments mapped according to mask and $column object
	 * function ($args, Column $column ) { ... }
	 *
	 * @param Link $link
	 * @param array $mask
	 * @return LinkAction
	 */
	public function setLink($link, array $mask = array())
	{
		if (!is_callable($link) && !$link instanceof Link) {
			throw new Nette\InvalidArgumentException("Link must be callable or instance of Nette\\Application\\UI\\Link");
		}

		$this->mask = $mask;
		$this->link = $link;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getLink()
	{
		if (!$this->link) {
			return NULL;
		}

		$args = array();
		foreach ($this->mask as $argName => $paramName) {
			$args[$argName] = $this->getGrid()->getRecordProperty($paramName);
		}

		if (is_callable($this->link)) {
			return call_user_func($this->link, $args, $this);
		}

		$link = clone $this->link;
		foreach ($args as $argName => $value) {
			$link->setParam($argName, $value);
		}

		return (string)$link;
	}



	/**
	 * @return Nette\Utils\Html|string
	 */
	public function getControl()
	{
		$value = $this->getValue();

		if (is_bool($value)) {
			return $this->getRenderer()->renderBoolean($value);

		} elseif ($value instanceof \DateTime) {
			return $this->getRenderer()->renderDateTime($value, $this->dateTimeFormat);
		}

		$link = $this->getLink();
		if ($link) {
			return Html::el('a', array(
				'href' => $link
			))->{$value instanceof Html ? 'add' : 'setText'}($value);
		}

		// other
		return Html::el()->setHtml(DefaultHelpers::escapeHtml($value));
	}

}