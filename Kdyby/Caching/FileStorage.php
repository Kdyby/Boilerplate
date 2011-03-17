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


namespace Kdyby\Caching;

use Nette;



class FileStorage extends Nette\Caching\FileStorage implements \ArrayAccess
{

	/**
	 * @param string $key
	 * @return \DateTime
	 */
    public function getCreateTime($key)
    {
        return new \DateTime(filemtime($this->getCacheFile($key)));
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
		throw new \NotSupportedException();
	}



	/**
	 * @param string $offset
	 */
	public function offsetExists($offset)
	{
		throw new \NotSupportedException();
	}



	/**
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		throw new \NotSupportedException();
	}

}