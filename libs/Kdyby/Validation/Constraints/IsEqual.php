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
class IsEqual extends Validation\BaseConstraint
{

	/** @var mixed */
	protected $value;



	/**
	 * @param mixed $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return $this->value == $other;
	}



	/**
	 * @return IsEqual
	 */
	public static function create($name, $property, $value)
	{
		return new static($value);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'is equal to ' . Kdyby\Tools\Mixed::toString($this->value, FALSE);
	}

}