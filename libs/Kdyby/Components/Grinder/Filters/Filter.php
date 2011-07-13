<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters;

use Kdyby;
use Nette;
use Nette\ComponentModel\IComponent;



/**
 * @author Filip Procházka
 *
 * @property bool $skipEmpty
 * @property string $type
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

	/** @var bool */
	private $skipEmpty = TRUE;

	/** @var mixed */
	private $value = FALSE;

	/** @var mixed */
	private $defaultValue;

	/** @var string */
	private $type;

	/** @var string */
	private $sqlType = 's';



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
	 * @return callback
	 */
	public function getMethod()
	{
		return $this->methodCallback;
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
	 * @return callback
	 */
	public function getSource()
	{
		return $this->sourceCallback;
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
	 * @param bool $skipEmpty
	 * @return Filter
	 */
	public function setSkipEmpty($skipEmpty)
	{
		$this->skipEmpty = (bool)$skipEmpty;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function getSkipEmpty()
	{
		return $this->skipEmpty;
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
	 * @return string
	 */
	public function getDefaultValue()
	{
		return $this->defaultValue;
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
	 * @param string $sqlType
	 * @return Filter
	 */
	public function setSqlType($sqlType)
	{
		$this->sqlType = $sqlType;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getSqlType()
	{
		return $this->sqlType;
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
				$value = array_map($this->typeMapperFactory($this->getType()), $value);

			} elseif ($value !== NULL) {
				settype($value, $this->getType());
			}
		}

		return $this->value = $value;
	}



	/**
	 * @param string $type
	 * @return \Closure
	 */
	private function typeMapperFactory($type)
	{
		return function ($value) use ($type) {
			if ($value !== NULL) {
				settype($value, $type);
			}

			return $value;
		};
	}



	/**
	 * @return array
	 */
	public function createFragments()
	{
		if (!is_callable($this->sourceCallback)) {
			throw new Nette\InvalidStateException("Given datasource is not callable, in filter " . $this->getName() . ".");
		}

		if (!is_callable($this->methodCallback)) {
			throw new Nette\InvalidStateException("Given fragment builder method is not callable, in filter " . $this->getName() . ".");
		}

		return call_user_func($this->methodCallback, $this->getValue(), $this);
	}

}