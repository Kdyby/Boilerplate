<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MissingServiceException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetNotFoundException extends \OutOfRangeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileNotFoundException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class NotSupportedException extends \LogicException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UnexpectedValueException extends \UnexpectedValueException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class InvalidDefinitionFileException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LatteCompileException extends \Nette\Latte\CompileException implements Exception
{

	/**
	 * @param string $message
	 * @param \Exception|null $previous
	 */
	public function __construct($message = NULL, \Exception $previous = NULL)
	{
		\Exception::__construct($previous ? $previous->getMessage() : $message, 0, $previous);
	}

}
