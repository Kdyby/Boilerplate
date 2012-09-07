<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Caching;

use Kdyby;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ServiceDecorator extends Nette\Object
{

	/**
	 * @var \Nette\Object
	 */
	protected $service;

	/**
	 * @var \Nette\Caching\Cache
	 */
	protected $cache;



	/**
	 * @param object $service
	 * @param \Nette\Caching\IStorage $cacheStorage
	 * @param string $namespace
	 */
	public function __construct($service, IStorage $cacheStorage, $namespace = NULL)
	{
		$this->service = $service;
		$this->cache = new Cache($cacheStorage, $namespace ?: get_class($service));
	}



	/**
	 * @param string $function
	 * @param array $args
	 * @param callback $dpCallback
	 */
	protected function decorate($function, array $args = array(), $dpCallback = NULL)
	{
		$callback = array($this->service, $function);
		$key = array(get_class($this->service), $function, $args);
		if (($data = $this->cache->load($key)) === NULL) {
			$data = $this->cache->save(
				$key,
				$data = callback($callback)->invokeArgs($args),
				$this->buildDeps($dpCallback, $data)
			);
		}
		return $data;
	}



	/**
	 * @param callback $dpCallback
	 * @param mixed $data
	 *
	 * @return array
	 */
	private function buildDeps($dpCallback, $data)
	{
		if ($dpCallback === NULL || $data === NULL) {
			return array();
		}

		return (array)callback($dpCallback)->invoke($data, $this->service);
	}



	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed|NULL
	 */
	public function __call($name, $arguments)
	{
		if (!method_exists($this->service, $name)) {
			return $this->service->__call($name, $arguments);
		}

		return $this->decorate($name, $arguments);
	}

}
