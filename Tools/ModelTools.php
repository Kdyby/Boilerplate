<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Tools;

use Nette;
use Nette\Environment;
use Nette\ObjectMixin;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class ModelTools extends Nette\Object
{

	const CACHED_PREFIX = 'c_';

	private static $methodCache;



	/**
	 * Call to cached or undefined method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws \MemberAccessException
	 * @throws \InvalidStateException
	 */
	public static function tryCall($_this, $name, $args)
	{
		if (substr($name, 0, strlen(self::CACHED_PREFIX)) != self::CACHED_PREFIX) {
			return ObjectMixin::call($_this, $name, $args);
		}

		$method = substr($name, strlen(self::CACHED_PREFIX));
		if (!\method_exists($_this, $method)){
			throw new \InvalidStateException('Invalid call for cached output of ' . get_class($_this) . '::' . $method . ', method not set.');
		}

		$reflection = new Nette\Reflection\MethodReflection($_this, $method);
		$annotations = $reflection->getAnnotations();

		if (isset($annotations['Cache'])) {
			$key = get_class($_this) . '::' . $method . '?' . md5(serialize($args));
			$cache = self::getMethodCache();

			if (!isset($cache[$key])) {
				$data = call_user_func_array(array($_this, $method), $args);

				if (is_array($annotations['Cache'])) {
					$depends = array();

					$options = $annotations['Cache'];
					$options = is_array($options) ? current($options) : array();
					$options = $options instanceof \ArrayObject ? $options->getArrayCopy() : $options;

					if (isset($annotations['Tags'])) {
						$tags = is_array($tags = $annotations['Tags']) ? current($tags) : array();
						$tags = $tags instanceof \ArrayObject ? $tags->getArrayCopy() : $tags;
						$options[Nette\Caching\Cache::TAGS] = $tags;
					}

					foreach($options as $index => $value) {
						$const = constant('Nette\Caching\Cache::' . strtoupper($index));
						if ($const) {
							$depends += array($const => $value);

						} else {
							throw new \InvalidArgumentException('Invalid cache options set for `' . $key . '`, dependency on `' . $index . '` not found.');
						}
					}
					$cache->save($key, $data, $depends);

				} else {
					$cache[$key] = $data;
				}
			}

			return $cache[$key];
		}

		return call_user_func_array(array($_this, $method), $args);
	}



	/**
	 * @return Nette\Caching\Cache
	 */
	private static function getMethodCache()
	{
		if (self::$methodCache === NULL) {
			self::$methodCache = Environment::getCache('Kdyby.ClassMethods.Data');
		}

		return self::$methodCache;
	}



	/**
	 * @param array $conditions
	 * @return <type>
	 */
	public static function cleanCache($conditions)
	{
		self::getMethodCache()->clean($conditions);
	}

}