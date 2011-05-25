<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class InvalidPresenterException extends Nette\Application\InvalidPresenterException
{

	const NO_MODULE = 1;
	const INVALID_NAME = 2;
	const MISSING = 3;
	const DOES_NOT_IMPLEMENT_INTERFACE = 4;
	const IS_ABSTRACT = 5;
	const CASE_SENSITIVE = 6;



	/**
	 * @param string $presenter
	 * @return InvalidPresenterException
	 */
	public static function presenterNoModule($presenter)
	{
		return new self("Presenter " . $presenter . " should be in Module.", self::NO_MODULE);
	}



	/**
	 * @param string $presenter
	 * @return InvalidPresenterException
	 */
	public static function classNoModule($class)
	{
		return new self("Presenter " . $class . " should be in Module.", self::NO_MODULE);
	}



	/**
	 * @param string $presenter
	 * @return InvalidPresenterException
	 */
	public static function invalidName($name)
	{
		return new self("Presenter name must be alphanumeric string, '$name' is invalid.", self::INVALID_NAME);
	}



	/**
	 * @param string $name
	 * @param string $class
	 * @param string $file
	 * @param \Exception $previous = NULL
	 * @return InvalidPresenterException
	 */
	public static function missing($name, $class, $file, \Exception $previous = NULL)
	{
		return new self("Cannot load presenter '$name', class '$class' was not found in '$file'.", self::MISSING, $previous);
	}



	/**
	 * @param string $name
	 * @param string $class
	 * @return InvalidPresenterException
	 */
	public static function doesNotImplementInterface($name, $class)
	{
		return new self("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.", self::DOES_NOT_IMPLEMENT_INTERFACE);
	}



	/**
	 * @param string $name
	 * @param string $class
	 * @return InvalidPresenterException
	 */
	public static function isAbstract($name, $class)
	{
		return new self("Cannot load presenter '$name', class '$class' is abstract.", self::IS_ABSTRACT);
	}



	/**
	 * @param string $name
	 * @param string $realName
	 * @return InvalidPresenterException
	 */
	public static function caseSensitive($name, $realName)
	{
		return new self("Cannot load presenter '$name', case mismatch. Real name is '$realName'.", self::CASE_SENSITIVE);
	}

}