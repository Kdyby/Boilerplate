<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Kdyby\Tools;

use Nette;



/**
 * Freezable array object
 *
 * @author	Patrik Votoček
 * @author Filip Procházka
 */
class FreezableArray extends Nette\FreezableObject implements \ArrayAccess, \Countable, \IteratorAggregate
{

	/** @var array */
	private $array = array();



	/**
	 * Returns an iterator over all items.
	 * 
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->array);
	}



	/**
	 * Returns items count.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->array);
	}



	/**
	 * @param callable $filter
	 * @return array
	 */
	public function filter($filter)
	{
		if (!is_callable($filter)) {
			throw new \InvalidArgumentException("Given filter is not callable");
		}

		return array_filter($this->array, callback($filter));
	}



	/**
	 * @param callable $mapper
	 * @return array
	 */
	public function map($mapper)
	{
		if (!is_callable($mapper)) {
			throw new \InvalidArgumentException("Given mapper is not callable");
		}

		return array_map(callback($mapper), $this->array);
	}



	/**
	 * Replaces or appends a item.
	 * 
	 * @param  string
	 * @param  mixed
	 * @return FreezableArray
	 */
	public function offsetSet($key, $value)
	{
		$this->updating();		
		$this->array[$key] = $value;
		return $this;
	}



	/**
	 * Returns a item.
	 * 
	 * @param  string
	 * @return mixed
	 * @throws \MemberAccessException
	 */
	public function offsetGet($key)
	{
		if (!$this->offsetExists($key)) {
			throw new \MemberAccessException("Cannot read an undeclared item {$class}['{$key}'].");
		}

		return $this->array[$key];
	}



	/**
	 * Determines whether a item exists.
	 * 
	 * @param  string
	 * @return bool
	 */
	public function offsetExists($key)
	{
		return array_key_exists($key, $this->array);
	}



	/**
	 * Removes the element at the specified position in this list.
	 * 
	 * @param  string
	 */
	public function offsetUnset($key)
	{
		$this->updating();
		unset($this->list[$key]);
	}

}
