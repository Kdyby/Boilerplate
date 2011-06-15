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
class StringStartsWith extends Validation\BaseConstraint
{

	/** @var string */
	protected $prefix;



	/**
	 * @param string $prefix
	 */
	public function __construct($prefix)
	{
		$this->prefix = $prefix;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return strpos($other, $this->prefix) === 0;
	}



	/**
	 * @return StringStartsWith
	 */
	public static function create($name, $property, $prefix)
	{
		return new static($prefix);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'starts with "' . $this->prefix . '"';
	}

}