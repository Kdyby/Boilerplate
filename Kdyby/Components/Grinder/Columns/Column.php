<?php

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