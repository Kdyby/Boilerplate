<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby;



/**
 * The Kdyby Framework (http://kdyby.org)
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
final class Framework
{

	const NAME = 'Kdyby Framework';
	const VERSION = '2.0a';
	const REVISION = '$WCREV$ released on $WCDATE$';



	/**
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}



	/**
	 * @return array
	 */
	public static function getDefaultPackages()
	{
		return array(
			'Kdyby\Package\FrameworkPackage\FrameworkPackage',
			'Kdyby\Package\DoctrinePackage\DoctrinePackage',
		);
	}



	/**
	 * @return \Kdyby\Packages\PackagesList
	 */
	public static function createPackagesList()
	{
		return new Packages\PackagesList(static::getDefaultPackages());
	}



	/**
	 * @return array
	 */
	public static function findExceptionClasses()
	{
		return iterator_to_array(\Nette\Utils\Finder::findFiles('exceptions.php')->in(__DIR__));
	}

}
