<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FilterManager extends Assetic\FilterManager
{

	/** @var \SystemContainer|\Nette\DI\Container */
	protected $container;

	/** @var array */
	protected $filterIds;



	/**
	 * @param \Nette\DI\Container $container
	 * @param array $filterIds
	 */
	public function __construct(Nette\DI\Container $container, array $filterIds = array())
	{
		$this->container = $container;
		$this->filterIds = $filterIds;
	}



	/**
	 * @param string $name
	 * @return \Assetic\Filter\FilterInterface
	 */
	public function get($name)
	{
		if (!isset($this->filterIds[$name])) {
			return parent::get($name);
		}

		return $this->container->getService($this->filterIds[$name]);
	}



	/**
	 * @param string $name
	 * @return bool
	 */
	public function has($name)
	{
		return isset($this->filterIds[$name]) || parent::has($name);
	}



	/**
	 * @return array
	 */
	public function getNames()
	{
		return array_unique(array_merge(array_keys($this->filterIds), parent::getNames()));
	}

}
