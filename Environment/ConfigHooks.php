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


namespace Kdyby;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class ConfigHooks extends Nette\Object implements \ArrayAccess
{

	/** @var array */
	private $hooks = array();



	/**
	 * @param array $hooks
	 */
	public function __construct(array $hooks)
	{
		$this->hooks = $hooks;
	}



	/**
	 * @param string $hook
	 * @return string
	 */
	public function getHook($hook)
	{
		return $this->hooks[str_replace('\\', '-', $hook)];
	}



	/********************* \ArrayAccess *********************/



	/**
	 * @param string $offset
	 * @param string $value
	 * @throws NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new \NotSupportedException("Setting config hook is not allowed. Write your directive to 'Kdyby.Core' section into your config manualy.");
	}



	/**
	 * @param string $offset
	 * @throws NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new NotImplementedException("Setting config hook is not allowed. Write your directive to 'Kdyby.Core' section into your config manualy.");
	}



	/**
	 * @param string $offset
	 * @return string
	 */
	public function offsetGet($offset)
	{
		return $this->getHook($offset);
	}



	/**
	 * @param string $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->hooks[$offset]);
	}


}