<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tools;

use Nette;
use Kdyby;



/**
 * Pokud je objekt zmrazen půjde pouze volat properties a metody
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LogicDelegator extends Nette\FreezableObject
{
	/** @var array */
	private $callbacks = array();

	/** @var mixed */
	private $delegate;



	/**
	 * @param mixed $delegate
	 */
	public function __construct($delegate = NULL)
	{
		$this->delegate = $delegate;
	}



	/**
	 * pokud je neznámá metoda zavolána s argumentem uložit do $this->callbacks
	 * pokud je neznámá metoda zavolána bez argumentu a je v poli callbacks tak zavolat a vrátit, jinak vyjímka
	 *
	 * @param string $method
	 * @param callable|NULL $callback
	 * @return LogicDelegator|mixed
	 */
	public function __call($method, $callback = NULL)
	{
		if (is_callable($callback)) {
			$this->updating();
			$this->callbacks[$method] = $callback;

			return $this;
		}

		return $this->callbacks[$method]($this->delegate);
	}



	/**
	 * pokud je čteno z neznámé property a je v poli callbacks tak zavolat a vrátit, jinak vyjímka
	 *
	 * @param string $property
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->callbacks[$name]($this->delegate);
	}



	/**
	 * pokud je do neznámé property ukládáno uložit do $this->callbacks
	 *
	 * @param string $property
	 * @param callable $callback
	 */
	public function __set($property, $callback)
	{
		$this->updating();

		if (is_callable($callback)) {
			$this->callbacks[$property] = $callback;
		}
	}



	public function __clone()
	{
		$this->updating();
	}

}
