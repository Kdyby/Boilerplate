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
class StringEndsWith extends Validation\BaseConstraint
{

	/** @var string */
	protected $suffix;



	/**
	 * @param string $suffix
	 */
	public function __construct($suffix)
	{
		$this->suffix = $suffix;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return substr($other, 0 - strlen($this->suffix)) == $this->suffix;
	}



	/**
	 * @return StringEndsWith
	 */
	public static function create($name, $property, $suffix)
	{
		return new static($suffix);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'ends with "' . $this->suffix . '"';
	}

}