<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\Constraints;

use Kdyby;
use Kdyby\Validation;
use Nette;



/**
 * @author Filip Procházka
 */
class ArrayHasKey extends Validation\BaseConstraint
{

	/** @var integer|string */
	protected $key;



	/**
	 * @param integer|string $key
	 */
	public function __construct($key)
	{
		$this->key = $key;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return array_key_exists($this->key, $other);
	}



	/**
	 * @return ArrayHasKey
	 */
	public static function create($name, $property, $key)
	{
		return new static($key);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'has the key "' . $this->key . '"';
	}

}