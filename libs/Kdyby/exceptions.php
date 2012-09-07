<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
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

	/**
	 * @param string $type
	 * @param string|object $class
	 * @param string $property
	 *
	 * @return \Kdyby\MemberAccessException
	 */
	public static function propertyNotWritable($type, $class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Cannot write to $type property $class::\$$property.");
	}



	/**
	 * @param string|object $class
	 *
	 * @return \Kdyby\MemberAccessException
	 */
	public static function propertyWriteWithoutName($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Cannot write to a class '$class' property without name.");
	}



	/**
	 * @param string $type
	 * @param string|object $class
	 * @param string $property
	 *
	 * @return \Kdyby\MemberAccessException
	 */
	public static function propertyNotReadable($type, $class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Cannot read $type property $class::\$$property.");
	}



	/**
	 * @param string|object $class
	 *
	 * @return \Kdyby\MemberAccessException
	 */
	public static function propertyReadWithoutName($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Cannot read a class '$class' property without name.");
	}



	/**
	 * @param string|object $class
	 *
	 * @return MemberAccessException
	 */
	public static function callWithoutName($class)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Call to class '$class' method without name.");
	}



	/**
	 * @param object|string $class
	 * @param string $method
	 *
	 * @return \Kdyby\MemberAccessException
	 */
	public static function undefinedMethodCall($class, $method)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Call to undefined method $class::$method().");
	}

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


	/**
	 * @param mixed $list
	 * @param string|object $class
	 * @param string $property
	 *
	 * @return \Kdyby\UnexpectedValueException
	 */
	public static function invalidEventValue($list, $class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Property $class::$$property must be array or NULL, " . gettype($list) . " given.");
	}



	/**
	 * @param string|object $class
	 * @param string $property
	 *
	 * @return \Kdyby\UnexpectedValueException
	 */
	public static function notACollection($class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Class property $class::\$$property is not an instance of Doctrine\\Common\\Collections\\Collection.");
	}



	/**
	 * @param string|object $class
	 * @param string $property
	 *
	 * @return \Kdyby\UnexpectedValueException
	 */
	public static function collectionCannotBeReplaced($class, $property)
	{
		$class = is_object($class) ? get_class($class) : $class;
		return new static("Class property $class::\$$property is an instance of Doctrine\\Common\\Collections\\Collection. Use add<property>() and remove<property>() methods to manipulate it or declare your own.");
	}

}



/**
 * The exception that is thrown when static class is instantiated.
 */
class StaticClassException extends \LogicException
{

}
