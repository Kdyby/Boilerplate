<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Config;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TaggedServices extends Nette\Object implements \Countable, \Iterator
{
	/**
	 * @var \Nette\DI\Container|\SystemContainer
	 */
	private $container;

	/**
	 * @var string
	 */
	private $tag;

	/**
	 * @var array
	 */
	private $services = array();

	/**
	 * @var array
	 */
	private $meta = array();



	/**
	 * @param string $tag
	 * @param \Nette\DI\Container $container
	 */
	public function __construct($tag, Nette\DI\Container $container)
	{
		$this->container = $container;
		$this->tag = $tag;

		$this->meta = $this->container->findByTag($this->tag);
		$this->services = array_keys($this->meta);
	}



	/**
	 * @param mixed $meta
	 * @return array|object[]
	 */
	public function findByMeta($meta)
	{
		$container = $this->container;
		return array_map(function ($name) use ($container) {
			/** @var \Nette\DI\Container $container */
			return $container->getService($name);

		}, array_keys(array_filter($this->meta, function ($current) use ($meta) {
			return $current === $meta;
		})));
	}



	/**
	 * @param mixed $meta
	 *
	 * @return object|NULL
	 */
	public function findOneByMeta($meta)
	{
		if (!$id = array_search($meta, $this->meta, TRUE)) {
			return NULL;
		}

		return $this->container->getService($id);
	}



	/**
	 * @param mixed $meta
	 *
	 * @return object|NULL
	 */
	public function createOneByMeta($meta)
	{
		if (!$id = array_search($meta, $this->meta, TRUE)) {
			return NULL;
		}

		$method = Nette\DI\Container::getMethodName($id, FALSE);
		if (method_exists($this->container, $method)) {
			return $this->container->$method();

		} else {
			$factory = $this->container->getService($id);
			return callback($factory)->invoke();
		}
	}


	/************************* \Countable *************************/


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->meta);
	}


	/************************* \Iterator *************************/


	/**
	 * @return bool|object
	 */
	public function current()
	{
		if ($name = current($this->services)) {
			return $this->container->getService($name);
		}

		return FALSE;
	}



	/**
	 * @return bool|object
	 */
	public function next()
	{
		next($this->services);
		return $this->current();
	}



	/**
	 * @return mixed
	 */
	public function key()
	{
		return key($this->services);
	}



	/**
	 * @return bool
	 */
	public function valid()
	{
		$key = key($this->services);
		return ($key !== NULL && $key !== FALSE);
	}



	/**
	 */
	public function rewind()
	{
		reset($this->services);
	}

}
