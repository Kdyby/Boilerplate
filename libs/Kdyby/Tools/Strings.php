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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
final class Strings extends Nette\Object
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}



	/**
	 * @param string $a
	 * @param string $b
	 * @return string
	 */
	public static function blend($a, $b)
	{
		$pos = strrpos($a, $b);
		if ($pos !== FALSE) { // is croping
			return substr($a, 0, $pos + strlen($b));

		} else { // is merging
			$fromRight = 0;
			do {
				$fromRight--;
				$pos = strrpos($a, $match = substr($b, 0, $fromRight));
			} while ($pos === FALSE && $match);

			return substr($a, 0, $pos + strlen($match)) . substr($b, $fromRight);
		}
	}



	/**
	 * Mirror of Nette\Utils\Strings
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		return callback('Nette\Utils\Strings', $name)->invokeArgs($args);
	}
}