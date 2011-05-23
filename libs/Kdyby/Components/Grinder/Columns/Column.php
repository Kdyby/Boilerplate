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



	public function addFilter($filter)
	{
		if (!is_callable($filter)) {
			throw new \InvalidArgumentException("Given filter is not callable, " . gettype($filter) . " given.");
		}

		$this->filters[] = $filter;
	}



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		$value = parent::getValue();

		foreach ($this->filters as $filter) {
			$value = call_user_func($filter, $value, $this);
		}

		return $value;
	}

}