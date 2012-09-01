<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;

use Assetic;
use Kdyby;
use Nette;
use Nette\DI\Container;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FilterManager extends Assetic\FilterManager
{

	/** @var \SystemContainer|\Nette\DI\Container */
	protected $container;

	/** @var array */
	protected $filterIds = array();



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}



	/**
	 * @param string $serviceId
	 * @param string $filterName
	 */
	public function registerFilterService($serviceId, $filterName)
	{
		$this->filterIds[$filterName] = $serviceId;
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
