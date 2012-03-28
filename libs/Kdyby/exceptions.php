<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby;



/**
 * The exception that is thrown when the value of an argument is
 * outside the allowable range of values as defined by the invoked method.
 */
class ArgumentOutOfRangeException extends \InvalidArgumentException
{

}



/**
 * The exception that is thrown when a method call is invalid for the object's
 * current state, method has been invoked at an illegal or inappropriate time.
 */
class InvalidStateException extends \RuntimeException
{

}



/**
 * The exception that is thrown when a requested method or operation is not implemented.
 */
class NotImplementedException extends \LogicException
{

}



/**
 * The exception that is thrown when an invoked method is not supported. For scenarios where
 * it is sometimes possible to perform the requested operation, see InvalidStateException.
 */
class NotSupportedException extends \LogicException
{

}



/**
 * The exception that is thrown when a requested method or operation is deprecated.
 */
class DeprecatedException extends NotSupportedException
{

}



/**
 * The exception that is thrown when accessing a class member (property or method) fails.
 */
class MemberAccessException extends \LogicException
{

}



/**
 * The exception that is thrown when an I/O error occurs.
 */
class IOException extends \RuntimeException
{

}



/**
 * The exception that is thrown when accessing a file that does not exist on disk.
 */
class FileNotFoundException extends IOException
{

	/**
	 * @param string $file
	 *
	 * @return \Kdyby\FileNotWritableException
	 */
	public static function fromFile($file)
	{
		return new static("Unable to read file '$file'. Please, make this file readable.");
	}

}



/**
 * The exception that is thrown when writing to a file that is not writable.
 */
class FileNotWritableException extends IOException
{

	/**
	 * @param string $file
	 * @return \Kdyby\FileNotWritableException
	 */
	public static function fromFile($file)
	{
		return new static("Unable to write to file '$file'. Please, make this file writable.");
	}

}



/**
 * The exception that is thrown when part of a file or directory cannot be found.
 */
class DirectoryNotFoundException extends IOException
{

	/**
	 * @param string $directory
	 *
	 * @return \Kdyby\DirectoryNotWritableException
	 */
	public static function fromDir($directory)
	{
		return new static("Unable to read directory '$directory'. Please, make this directory readable.");
	}

}



/**
 * The exception that is thrown when writing to a directory that is not writable.
 */
class DirectoryNotWritableException extends IOException
{

	/**
	 * @param string $directory
	 * @return \Kdyby\DirectoryNotWritableException
	 */
	public static function fromDir($directory)
	{
		return new static("Unable to write to directory '$directory'. Please, make this directory writable.");
	}

}



/**
 * The exception that is thrown when an argument does not match with the expected value.
 */
class InvalidArgumentException extends \InvalidArgumentException
{

}



/**
 * The exception that is thrown when an illegal index was requested.
 */
class OutOfRangeException extends \OutOfRangeException
{

}



/**
 * The exception that is thrown when a value (typically returned by function) does not match with the expected value.
 */
class UnexpectedValueException extends \UnexpectedValueException
{

}



/**
 * The exception that is thrown when static class is instantiated.
 */
class StaticClassException extends \LogicException
{

}
