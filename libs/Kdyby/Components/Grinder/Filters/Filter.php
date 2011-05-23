<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Kdyby;
use Nette;
use Nette\ComponentModel\IComponent;



/**
 * @author Filip Procházka
 *
 * @property-read FiltersMap $map
 * @property-read string $name
 * @property-read string $column
 * @property-read IComponent $control
 * @property-read mixed $defaultValue
 */
class Filter extends Nette\Object implements IFilter
{

	/** @var FiltersMap */
	private $map;

	/** @var string */
	private $name;

	/** @var string|array */
	private $column;

	/** @var Nette\Callback */
	private $sourceCallback;

	/** @var Nette\Callback */
	private $methodCallback;

	/** @var IComponent */
	private $control;

	/** @var mixed */
	private $value = FALSE;

	/** @var mixed */
	private $defaultValue;

	/** @var string */
	private $type;



	/**
	 * @param FiltersMap $map
	 * @param string $name
	 * @param string $column
	 */
	public function __construct(FiltersMap $map, $name, $column)
	{
		$this->map = $map;
		$this->name = $name;
		$this->column = $column;
	}



	/**
	 * @return FiltersMap
	 */
	public function getMap()
	{
		return $this->map;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string|array
	 */
	public function getColumn()
	{
		return $this->column;
	}



	/**
	 * @param callback $callback
	 * @return Filter
	 */
	public function setMethod($callback)
	{
		$this->methodCallback = callback($callback);
		return $this;
	}



	/**
	 * @param callback $callback
	 * @return Filter
	 */
	public function setSource($callback)
	{
		$this->sourceCallback = callback($callback);
		return $this;
	}



	/**
	 * @param IComponent $control
	 * @return Filter
	 */
	public function setControl(IComponent $control)
	{
		$this->control = $control;
		return $this;
	}



	/**
	 * @return IComponent
	 */
	public function getControl()
	{
		return $this->control;
	}



	/**
	 * @return string
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
	}



	/**
	 * @param string $defaultValue
	 * @return Filter
	 */
	public function setDefaultValue($defaultValue)
	{
		$this->defaultValue = $defaultValue;
		return $this;
	}



	/**
	 * @param string $type
	 * @return Filter
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}



	/**
	 * @return string
	 */
	public function getParameterName()
	{
		return 'filter' . ucfirst($this->name);
	}



	/**
	 * @return mixed
	 */
	public function getValue()
	{
		if ($this->value !== FALSE) {
			return $this->value;
		}

		$value = call_user_func($this->sourceCallback);
		$value = $value !== NULL ? $value : $this->getDefaultValue();

		if ($this->getType() !== NULL) {
			if (is_array($value)) {
				$type = $this->getType();
				array_map(function ($value) use ($type) {
					if ($value !== NULL) {
						settype($value, $type);
					}

					return $value;
				}, $value);

			} elseif ($value !== NULL) {
				settype($value, $this->getType());
			}
		}

		return $this->value = $value;
	}



	/**
	 * @param QueryBuilder $qb
	 */
	public function apply(QueryBuilder $qb)
	{
		if (!is_callable($this->sourceCallback)) {
			throw new Nette\InvalidStateException("Given datasource is not callable, in filter " . $this->getName() . ".");
		}

		if (!is_callable($this->methodCallback)) {
			throw new Nette\InvalidStateException("Given fragment builder method is not callable, in filter " . $this->getName() . ".");
		}

		foreach ((array)call_user_func($this->methodCallback, $this->getValue(), $this, $qb) as $method) {
			if ($method instanceof Expr\Select) {
				$qb->add('select', $method, TRUE);

			} elseif ($method instanceof Expr\Join) {
				$qb->add('join', $method, TRUE);

			} else {
				$qb->andWhere($method);
			}
		}
	}

}