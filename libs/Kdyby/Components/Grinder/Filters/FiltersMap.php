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



/**
 * @author Filip Procházka
 */
class FiltersMap extends Nette\Object implements \IteratorAggregate
{

	/** @var array */
	private static $defaultMethodShortcuts = array(
		'=' => 'equals',
		'<' => 'lower',
		'<=' => 'lowerOrEquals',
		'>' => 'higher',
		'>=' => 'higherOrEquals',
		'~' => 'like',
	);

	/** @var array */
	private $map = array();

	/** @var IFragmentsBuilder */
	private $fragmentsBuilder;



	/**
	 * @param IFragmentsBuilder $fragmentsBuilder
	 */
	public function __construct(IFragmentsBuilder $fragmentsBuilder)
	{
		$this->fragmentsBuilder = $fragmentsBuilder;
	}



	/**
	 * @return IFragmentsBuilder
	 */
	public function getFragmentsBuilder()
	{
		return $this->fragmentsBuilder;
	}



	/**
	 * @param string $name
	 * @param string|array $column
	 * @param callback|NULL $dataSource
	 * @param callback|string|NULL $method
	 * @return Filter
	 */
	public function create($name, $column, $dataSource = NULL, $method = NULL)
	{
		$name = $name ?: str_replace('-', '', Nette\Utils\Strings::webalize($column));

		$filter = new Filter($this, $name, $column);

		if ($dataSource) {
			$filter->setSource($dataSource);
		}

		if ($method) {
			if (is_string($method)) {
				if (isset(self::$defaultMethodShortcuts[$method])) {
					$method = self::$defaultMethodShortcuts[$method];
				}

				$method = array($this->fragmentsBuilder, 'build' . ucfirst($method));
			}

			$filter->setMethod($method);
		}

		return $this->add($filter);
	}



	/**
	 * @param IFilter $filter
	 * @return IFilter
	 */
	public function add(IFilter $filter)
	{
		if (!is_string($filter->getName())) {
			throw new Nette\InvalidArgumentException("Filter name must be string, " . gettype($filter->getName()) . " given.");

		} elseif (!preg_match('~^[a-z0-9_-]+$~i', $filter->getName())) {
			throw new Nette\InvalidArgumentException("Filter name must be non-empty alphanumeric string, " . $filter->getName() . " given.");
		}

		if (isset($this->map[$filter->getName()])) {
			throw new Nette\OutOfRangeException("Name " . $filter->getName() . " is already taken");
		}

		return $this->map[$filter->getName()] = $filter;
	}



	/**
	 * @param string $name
	 * @return IFilter
	 */
	public function get($name)
	{
		if (!$this->has($name)) {
			throw new Nette\OutOfRangeException("Name " . $name . " is not defined");
		}

		return $this->map[$name];
	}



	/**
	 * @param string $name
	 * @return boolean
	 */
	public function has($name)
	{
		return isset($this->map[$name]);
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->map);
	}

}