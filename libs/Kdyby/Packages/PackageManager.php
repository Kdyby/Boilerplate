<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages;

use Kdyby;
use Nette;
use Nette\Utils\Finder;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackageManager extends Nette\Object
{

	/** @var \Kdyby\Packages\PackagesContainer */
	private $packages;



	/**
	 * @param \Kdyby\Packages\PackagesContainer $packages
	 */
	public function setActive(PackagesContainer $packages)
	{
		$this->packages = $packages;
	}



	/**
	 * @param string $name
	 * @return \Kdyby\Packages\Package
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function getPackage($name)
	{
		if (!isset($this->packages[$name])) {
			throw new Kdyby\InvalidArgumentException('Package named "' . $name . '" is not active.');
		}

		return $this->packages[$name];
	}



	/**
	 * Checks if a given class name belongs to an active package.
	 *
	 * @param string $class
	 *
	 * @return boolean
	 */
	public function isClassInActivePackage($class)
	{
		foreach ($this->packages as $package) {
			if (strpos($class, $package->getNamespace()) === 0) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Returns the file path for a given resource.
	 *
	 * A Resource can be a file or a directory.
	 *
	 * The resource name must follow the following pattern:
	 *
	 *	 @<PackageName>/path/to/a/file.something
	 *
	 * Looks first into Resources directory, than into package root.
	 *
	 * @param string $name  A resource name to locate
	 *
	 * @return string|array
	 */
	public function locateResource($name)
	{
		if ($name[0] !== '@') {
			throw new Kdyby\InvalidArgumentException('A resource name must start with @ ("' . $name . '" given).');
		}

		if (strpos($name, '..') !== FALSE) {
			throw new Kdyby\InvalidArgumentException('File name "' . $name . '" contains invalid characters (..).');
		}

		$packageName = substr($name, 1);
		$path = '';
		if (strpos($packageName, '/') !== FALSE) {
			list($packageName, $path) = explode('/', $packageName, 2);
		}

		$package = $this->packageManager->getPackage($packageName);
		if (file_exists($file = rtrim($package->getPath . '/Resources/' . $path, '/'))) {
			return $file;

		} elseif (file_exists($file = rtrim($package->getPath . '/' . $path, '/'))) {
			return $file;
		}


		throw new Kdyby\InvalidArgumentException('Unable to find file "' . $name . '".');
	}

}
