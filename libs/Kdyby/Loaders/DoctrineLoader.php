<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Loaders;

use Doctrine;



/**
 * @author Filip Procházka
 */
class DoctrineLoader
{

	/** @var array */
	private static $registered = FALSE;



	/**
	 * @param string|NULL $namespace
	 * @return Kdyby\Loaders\DoctrineLoader
	 */
	public static function register()
	{
		if (self::$registered) {
			throw DoctrineLoaderException::alreadyRegistered();
		}

		require_once LIBS_DIR . '/Doctrine/Common/ClassLoader.php';

		$commonLoader = self::$registered[] = new Doctrine\Common\ClassLoader('Doctrine', LIBS_DIR);
		$commonLoader->register();

		$commonLoader = self::$registered[] = new Doctrine\Common\ClassLoader('Doctrine\DBAL\Migrations', LIBS_DIR . '/Doctrine'); // migrations
		$commonLoader->register();

		$commonLoader = self::$registered[] = new Doctrine\Common\ClassLoader('DoctrineExtensions', LIBS_DIR . '/Doctrine');
		$commonLoader->register();

		$commonLoader = self::$registered[] = new Doctrine\Common\ClassLoader('Gedmo', LIBS_DIR . '/Doctrine');
		$commonLoader->register();

		return new self;
	}

}



/**
 * @author Filip Procházka
 */
class DoctrineLoaderException extends \Exception
{

	/**
	 * @return Kdyby\Loaders\DoctrineLoaderException
	 */
	public static function alreadyRegistered()
	{
		return new self("Cannot register, already registered loader for Doctrine");
	}

}
