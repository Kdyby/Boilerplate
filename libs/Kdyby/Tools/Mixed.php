<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Mixed extends Nette\Object
{

	/**
	 * Static class - cannot be instantiated.
	 *
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new Kdyby\StaticClassException;
	}



	/**
	 * @param mixed $value
	 * @return string
	 */
	public static function getType($value)
	{
		return is_object($value) ? 'instanceof ' . get_class($value) : gettype($value);
	}



	/**
	 * @param mixed $value
	 * @param boolean $short
	 * @return string
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
