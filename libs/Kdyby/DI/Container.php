<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Symfony\Component\Console;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Container extends Nette\DI\Container
{

	/**
	 * @param string $key
	 * @param string|NULL $default
	 * @throws Nette\OutOfRangeException
	 * @return mixed
	 */
	public function getParam($key, $default = NULL)
	{
		if (isset($this->params[$key])) {
			return $this->params[$key];

		} elseif (func_num_args()>1) {
			return $default;
		}

		throw new Nette\OutOfRangeException("Missing key '$key' in " . get_class($this) . '->params');
	}



	/**
	 * @param string $name
	 * @param Nette\DI\IContainer $container
	 */
	public function lazyCopy($name, Nette\DI\IContainer $container)
	{
		$this->addService($name, function() use ($name, $container) {
			return $container->getService($name);
		});
	}



	/**
	 * Adds the specified service or service factory to the container.
	 * @param  string
	 * @param  mixed   object, class name or callback
	 * @param  mixed   array of tags or string typeHint
	 * @return Container|ServiceBuilder  provides a fluent interface
	 */
	public function addService($name, $service, $tags = NULL)
	{
		if (substr_count($name, '.') !== 0) {
			throw new Nette\InvalidArgumentException("Service name cannot contain dot.");
		}

		return parent::addService($name, $service, $tags);
	}



	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 */
	public function getService($name)
	{
		if (substr_count($name, '.') === 0) {
			return parent::getService($name);
		}

		list($containerName, $serviceName) = explode('.', $name, 2);
		$container = parent::getService($containerName);
		if (!$container instanceof Nette\DI\IContainer) {
			throw new Nette\DI\MissingServiceException("Container '$containerName' not found.");
		}

		return $container->getService($serviceName);
	}



	/**
	 * Does the service exist?
	 * @param  string service name
	 * @return bool
	 */
	public function hasService($name)
	{
		if (substr_count($name, '.') === 0) {
			return parent::hasService($name);
		}

		list($containerName, $serviceName) = explode('.', $name, 2);
		$container = parent::getService($containerName);
		if (!$container instanceof Nette\DI\IContainer) {
			throw new Nette\DI\MissingServiceException("Container '$name' not found.");
		}

		return $container->hasService($serviceName);
	}

}