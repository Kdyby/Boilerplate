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
class IsEmail extends MatchesRegExpPattern implements Validation\IConstraint
{

	/**
	 * @author David Grudl
	 * @package Nette Framework (http://nette.org)
	 * @see https://github.com/nette/nette
	 *
	 * @param string $string
	 */
	public function __construct()
	{
		$atom = "[-a-z0-9!#$%&'*+/=?^_`{|}~]"; // RFC 5322 unquoted characters in local-part
		$localPart = "(?:\"(?:[ !\\x23-\\x5B\\x5D-\\x7E]*|\\\\[ -~])+\"|$atom+(?:\\.$atom+)*)"; // quoted or unquoted
		$chars = "a-z0-9\x80-\xFF"; // superset of IDN
		$domain = "[$chars](?:[-$chars]{0,61}[$chars])"; // RFC 1034 one domain component

		$this->pattern = "(^$localPart@(?:$domain?\\.)+[-$chars]{2,19}\\z)i";
	}



	/**
	 * @return IsEmail
	 */
	public static function create()
	{
		return new static();
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'is email ';
	}

}