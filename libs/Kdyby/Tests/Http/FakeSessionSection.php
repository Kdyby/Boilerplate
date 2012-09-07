<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Http;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FakeSessionSection extends Nette\Object implements \IteratorAggregate, \ArrayAccess
{

	/** @var \Nette\Http\Session */
	private $session;

	/** @var string */
	private $name;

	/** @var array */
	private $data = array();

	/** @var array */
	private $meta = array();

	/** @var bool */
	public $warnOnUndefined = FALSE;



	/**
	 * @param \Nette\Http\Session $session
	 * @param string $name
	 */
	public function __construct(Nette\Http\Session $session, $name)
	{
		if (!is_string($name)) {
			throw new Nette\InvalidArgumentException("Session namespace must be a string, " . gettype($name) . " given.");
		}

		$this->session = $session;
		$this->name = $name;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
	}



	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function &__get($name)
	{
		if ($this->warnOnUndefined && !array_key_exists($name, $this->data)) {
			trigger_error("The variable '$name' does not exist in session section", E_USER_NOTICE);
		}

		return $this->data[$name];
	}



	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}



	/**
	 * @param string $name
	 *
	 * @return void
	 */
	public function __unset($name)
	{
		unset($this->data[$name], $this->meta[$name]);
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function offsetSet($name, $value)
	{
		$this->__set($name, $value);
	}



	/**
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function offsetGet($name)
	{
		return $this->__get($name);
	}



	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function offsetExists($name)
	{
		return $this->__isset($name);
	}



	/**
	 * @param string $name
	 */
	public function offsetUnset($name)
	{
		$this->__unset($name);
	}



	/**
	 * @param int|string $time
	 * @param array $variables
	 *
	 * @return \Kdyby\Tests\Http\FakeSessionSection
	 */
	public function setExpiration($time, $variables = NULL)
	{
		return $this;
	}



	/**
	 * @param array $variables
	 */
	public function removeExpiration($variables = NULL)
	{

	}



	/**
	 * Cancels the current session section.
	 * @return void
	 */
	public function remove()
	{
		$this->data = NULL;
		$this->meta = NULL;
	}

}
