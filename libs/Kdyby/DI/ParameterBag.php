<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ParameterBag extends Nette\Object implements \ArrayAccess
{

	/** @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface */
	private $bag;



	/**
	 * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $bag
	 */
	public function __construct(ParameterBagInterface $bag)
	{
		$this->bag = $bag;
	}



	/**
	 * @param mixed $offset
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->bag->has(strtolower($offset));
	}



	/**
	 * @param mixed $offset
	 * @return mixed Can return all value types.
	 */
	public function offsetGet($offset)
	{
		return $this->bag->get(strtolower($offset));
	}



	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @return void
	 */
	public function offsetSet($offset, $value)
	{
		$this->bag->set(strtolower($offset), $value);
	}



	/**
	 * @param mixed $offset
	 * @return void
	 */
	public function offsetUnset($offset)
	{
		$this->bag->set(strtolower($offset), NULL);
	}

}
