<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Mixed extends Nette\Object
{

	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}



	/**
	 * @param mixed $object
	 */
	public static function getType($value)
	{
		return is_object($value) ? get_class($value) : gettype($value);
	}



	/**
	 * @param mixed $value
	 * @param boolean $short
	 * @return strng
	 */
	public static function toString($value, $short = FALSE)
	{
		if (is_array($value) || is_object($value)) {
			if (!$short) {
				return "\n" . print_r($value, TRUE);
			}

			return is_array($value) ? 'array(' . count($value) . ')' : get_class($value);
		}

		if (is_string($value) && strpos($value, "\n") !== FALSE) {
			return 'text';
		}

		$value = is_null($value) ? 'NULL' : $value;
		$value = $value === TRUE ? 'TRUE' : $value;
		$value = $value === FALSE ? 'FALSE' : $value;

		return $value . (!is_null($value) ? ' (' . gettype($value) . ')' : '');
	}



	/**
	 * @param mixed $value
	 * @return boolean
	 */
	public static function isSerializable($value)
	{
		return is_scalar($value);
	}

}