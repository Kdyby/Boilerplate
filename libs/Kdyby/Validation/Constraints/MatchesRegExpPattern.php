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
class MatchesRegExpPattern extends Validation\BaseConstraint
{

	/** @var string */
	protected $pattern;



	/**
	 * @param string $pattern
	 */
	public function __construct($pattern)
	{
		$this->pattern = $pattern;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return preg_match($this->pattern, $other) > 0;
	}



	/**
	 * @return MatchesRegExpPattern
	 */
	public static function create($name, $property, $pattern)
	{
		return new static($pattern);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'matches PCRE pattern "' . $this->pattern . '"';
	}

}