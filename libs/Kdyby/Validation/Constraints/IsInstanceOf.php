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
class IsInstanceOf extends Validation\BaseConstraint
{

	/** @var string */
	protected $className;



	/**
	 * @param string $className
	 */
	public function __construct($className)
	{
		$this->className = $className;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return ($other instanceof $this->className);
	}



	/**
	 * @return IsInstanceOf
	 */
	public static function create($name, $property, $className)
	{
		return new static($className);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'is instance of class "' . $this->className . '"';
	}

}