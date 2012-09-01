<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Packages;

use Kdyby;
use Nette;
use Nette\Utils\Finder;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PackageManager extends Nette\Object
{

	/** @var \Kdyby\Packages\PackagesContainer|\Kdyby\Packages\Package[] */
	private $packages;



	/**
	 * @param \Kdyby\Packages\PackagesContainer $packages
	 */
	public function setActive(PackagesContainer $packages)
	{
		$this->packages = $packages;
	}



	/**
	 * @return \Kdyby\Packages\Package[]
	 */
	public function getPackages()
	{
		return $this->packages->getPackages();
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
				return class_exists($class);
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
	 * @return array
	 * @throws \Kdyby\InvalidArgumentException
	 */
	protected function formatResourcePaths($name)
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

		$package = $this->getPackage($packageName);
		return array(
			$package->getPath() . '/' . $path,
			$package->getPath() . '/Resources/' . $path,
			$package->getPath() . '/Resources/public/' . $path,
		);
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
	 * @throws \Kdyby\InvalidArgumentException
	 * @return string|array
	 */
	public function locateResource($name)
	{
		foreach ($this->formatResourcePaths($name) as $path) {
			if (file_exists($path)) {
				return $path;
			}
		}

		throw new Kdyby\InvalidArgumentException('Unable to find file "' . $name . '".');
	}

}
