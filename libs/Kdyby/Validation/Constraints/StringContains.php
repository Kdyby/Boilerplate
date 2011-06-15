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
class StringContains extends Validation\BaseConstraint
{

	/** @var string */
	protected $string;

	/** @var boolean */
	protected $ignoreCase;



	/**
	 * @param string $string
	 * @param boolean $ignoreCase
	 */
	public function __construct($string, $ignoreCase = FALSE)
	{
		$this->string = $string;
		$this->ignoreCase = $ignoreCase;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		if ($this->ignoreCase) {
			return stripos($other, $this->string) !== FALSE;
		} else {
			return strpos($other, $this->string) !== FALSE;
		}
	}



	/**
	 * @return StringContains
	 */
	public static function create($name, $property, $string, $ignoreCase = FALSE)
	{
		return new static($string, $ignoreCase);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'contains "' . ($this->ignoreCase ? strtolower($this->string) : $this->string) . '"';
	}

}