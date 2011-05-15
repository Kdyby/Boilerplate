<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Caching;

use ArrayAccess;
use DateTime;
use Nette;



/**
 * @author Filip Procházka
 */
class FileStorage extends Nette\Caching\Storages\FileStorage implements ArrayAccess
{

	/**
	 * @param string $key
	 * @return \DateTime
	 */
    public function getCreateTime($key)
    {
        return new DateTime(filemtime($this->getCacheFile($key)));
    }



	/**
	 * @param string $namespace
	 * @return Nette\Caching\Cache
	 */
	public function offsetGet($namespace)
	{
		return new Nette\Caching\Cache(
			$this,
			$namespace
		);
	}



	/**
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		throw new Nette\NotSupportedException();
	}



	/**
	 * @param string $offset
	 */
	public function offsetExists($offset)
	{
		throw new Nette\NotSupportedException();
	}



	/**
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		throw new Nette\NotSupportedException();
	}

}