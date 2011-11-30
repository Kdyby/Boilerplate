<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Symfony;
use Symfony\Component\Console;
use Doctrine;
use Kdyby;
use Kdyby\Caching\CacheServices;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read array $parameters
 */
class Container extends Symfony\Component\DependencyInjection\Container implements IContainer
{

	/********************* Nette\DI\IContainer *********************/


    /**
     * Adds the specified service or service factory to the container.
     * @param string $name
     * @param mixed $service
     * @return void
     */
	public function addService($name, $service)
	{
		$this->set($name, $service);
	}



	/**
	 * Gets the service object of the specified type.
	 * @param string $name
	 * @return mixed
	 */
	public function getService($name)
	{
		return $this->get($name);
	}



	/**
	 * Removes the specified service type from the container.
	 * @param string $name
	 * @return void
	 */
	public function removeService($name)
	{
		$this->set($name, NULL);
	}



	/**
	 * Does the service exist?
	 * @param string $name
	 * @return bool
	 */
	public function hasService($name)
	{
		return $this->has($name);
	}



	/********************* shortcuts *********************/



	/**
	 * Expands %placeholders% in string.
	 * @param mixed $s
	 * @return mixed
	 */
	public function expand($s)
	{
		return $this->getParameterBag()->resolveValue($s);
	}



	/**
	 * Gets the service object, shortcut for getService().
	 * @param string $name
	 * @return object
	 */
	public function __get($name)
	{
		if ($name === 'params' || $name === 'parameters') {
			return $this->getParameterBag()->all();
		}

		return $this->getService($name);
	}



	/**
	 * Does the service exist?
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return $this->hasService($name);
	}

}
