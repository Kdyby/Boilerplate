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


namespace Kdyby\Gateway;

use Nette;



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