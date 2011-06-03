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
class ExceptionFactory extends Nette\Object
{

	/**
	 * @param integer $argument
	 * @param string $type
	 * @param mixed $value
	 * @return Nette\InvalidArgumentException
	 */
	public static function invalidArgument($argument, $type, $value = NULL)
	{
		$stack = debug_backtrace(FALSE);

		return new Nette\InvalidArgumentException(
			sprintf('Argument #%d%sof %s::%s() must be a %s',
				$argument,
				$value !== NULL ? ' (' . $value . ')' : ' ',
				$stack[1]['class'],
				$stack[1]['function'],
				$type
			)
		);
	}

}