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

}