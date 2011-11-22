<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Loaders;

use Kdyby;
use Kdyby\Iterators\TypeIterator;
use Nette;
use Nette\Caching\Cache;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RobotLoader extends Nette\Loaders\RobotLoader
{

	const CACHE_NAMESPACE = 'Nette.RobotLoader';



	/**
	 * @return \DateTime
	 */
	public function getIndexCreateTime()
	{
		$key = is_scalar($key = $this->getKey()) ? $key : serialize($key);
		$key = self::CACHE_NAMESPACE . Cache::NAMESPACE_SEPARATOR . md5($key);
		return $this->getCacheStorage()->getCreateTime($key);
	}



	/**
	 * @return TypeIterator
	 */
	public function createIndexFilter()
	{
		$classes = new \ArrayIterator(array_keys($this->getIndexedClasses()));
		return new TypeIterator($classes);
	}

}