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
class StringMatches extends MatchesRegExpPattern implements Validation\IConstraint
{

	/** @var string */
	protected $string;



	/**
	 * @param string $string
	 */
	public function __construct($string)
	{
		$this->pattern = preg_quote(preg_replace('/\r\n/', "\n", $string), '/');
		$this->pattern = str_replace(array(
				'%e',
				'%s',
				'%S',
				'%a',
				'%A',
				'%w',
				'%i',
				'%d',
				'%x',
				'%f',
				'%c'
			), array(
				'\\' . DIRECTORY_SEPARATOR,
				'[^\r\n]+',
				'[^\r\n]*',
				'.+',
				'.*',
				'\s*',
				'[+-]?\d+',
				'\d+',
				'[0-9a-fA-F]+',
				'[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?',
				'.'
			), $this->pattern
		);

		$this->pattern = '/^' . $this->pattern . '$/s';
		$this->string  = $string;
	}



	/**
	 * @param array $args
	 * @return StringMatches
	 */
	public static function create($name, $property, $string)
	{
		return new static($string);
	}

}