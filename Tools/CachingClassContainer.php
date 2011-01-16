<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Tools;

use Nette;
use Nette\Environment;
use Nette\Context;
use Nette\Reflection\ClassReflection;
use Nette\String;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class CachingClassContainer extends Nette\Object
{

	/** @var object */
	private $object;

	/** @var array */
	private $methods = array();

	/** @var array */
	private $annotations = array();


	/** @var Nette\Caching\Cache */
	private $cache;

	/** @var Nette\Caching\Cache */
	private $memcache;



	/**
	 * @param object $object
	 */
	public function __construct($object)
	{
		$this->object = $object;
		$this->methods = get_class_methods($methods);
	}



	/**
	 * @param string $method
	 * @return array
	 */
	private function getMethodAnnotations($method)
	{
		if (!$this->annotations) {
			$this->annotations = $this->getClassPropertiesAnnotations($this->object);
		}

		return $this->annotations[$method];
	}



	/**
	 * @param string $method
	 * @param array $params
	 */
	public function __call($method, $params)
	{
		if (!in_array($method, $this->methods)) {
			throw new \BadMethodCallException("Method $method is either not not accesible or doesn't exist");
		}

		$data = NULL;

		$annotations = $this->getMethodAnnotations($method);
		foreach ($annotations as $type => $options) {
			$options = is_array($options) ? current($options) : array();
			$options = $options instanceof \ArrayObject ? $options->getArrayCopy() : $options;

			$methodCallKey = md5(serialize($params));

			if ($type == 'Cache') {
				$cache = $this->getCache();
				if (isset($cache[$methodCallKey])) {
//					$data = $cache[$methodCallKey];
					dump("has");

				} else {
//					$data = call_user_func_array(array($this->object, $method), $params);
//					$cache->save($methodCallKey, $data);
					dump("getting");
				}
				// todo: dodělat ukládání a načítání

			} elseif ($type == 'Memcache') {
				$cache = $this->getMemcache();
				// todo: dodělat ukládání a načítání

			} elseif ($type == 'Validator') { // fuck yeah!

			}
		}

		return $data;
	}



	/**
	 * @param string $namespace
	 * @return 
	 */
	private function getCache()
	{
		if ($this->cache === NULL) {
			$this->cache = Environment::getCache($namespace);
		}

		return $this->cache;
	}



	/**
	 * @param string $namespace
	 * @return
	 */
	private function getMemcache()
	{
		if ($this->memcache === NULL) {
			$namespace = 'Kdyby.MethodCache.'.trim(String::webalize(get_class($this->object)), '-');
			$storage = $this->createMemcacheStorage($namespace);
			$this->memcache = new Nette\Caching\Cache($storage, $namespace);
		}

		return $this->memcache;
	}



	/**
	 * @param object $class
	 * @return array
	 */
	private function getClassPropertiesAnnotations($class)
	{
		$properties = array();
		$class = new ClassReflection($class);
		foreach ($class->getProperties() as $property) {
			$properties[$property->getName()] = $property->getAnnotations();
		}

		return $properties;
	}

}
