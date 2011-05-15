<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Gateway;

use Nette;



/**
 * @author Filip Procházka
 */
abstract class Request extends Nette\Object
{

	/** @var array */
	private $options = array();



	/**
	 * @param string $option
	 * @param mixed $value
	 */
	public function __set($option, $value)
	{
		$this->setOption($option, $value);
	}



	/**
	 * @param array $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->getOption($name);
	}



	/**
	 * @return bool
	 */
	public function __isset($option)
	{
		return isset($this->options[$option]);
	}



	/**
	 * @param string $option
	 * @param mixed $value
	 */
	public function setOption($option, $value)
	{
		$this->options[$option] = $value;
	}



	/**
	 * @param string $option
	 * @return mixed
	 */
	public function getOption($option)
	{
		return isset($this->options[$option]) ? $this->options[$option] : NULL;
	}



	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

}