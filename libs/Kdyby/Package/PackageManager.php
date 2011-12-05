<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package;

use Kdyby;
use Nette;
use Nette\Utils\Finder;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackageManager extends Nette\Object
{

	/** @var \Kdyby\Package\IPackage[] */
	private $packages = array();

	/** @var \Kdyby\Package\IMetadataStorage */
	private $metadataStorage;



	/**
	 */
	public function __construct()
	{
		$this->metadataStorage = new MetadataStorage\MemoryStorage();
	}


	/**
	 * @param array $packages
	 * @return \Kdyby\Package\IPackage[]
	 */
	public function activate(array $packages)
	{
		foreach ($packages as $packageClass) {
			if (isset($this->packages[$packageClass])) {
				throw new Kdyby\InvalidArgumentException("Package '$packageClass' is already active.");
			}

			$package = new $packageClass;
			if (!$package instanceof IPackage) {
				throw new Kdyby\InvalidArgumentException("Package '$packageClass' does not implement 'Kdyby\\Package\\IPackage'.");
			}

			$this->packages[$package->getName()] = $package;
		}

		return $this->packages;
	}



	/**
	 * @return \Kdyby\Package\IPackage[]
	 */
	public function getPackages()
	{
		return $this->packages;
	}



	/**
	 * @param string $packageName
	 * @return \Kdyby\Package\IPackage
	 */
	public function getPackage($packageName)
	{
		if (!isset($this->packages[$packageName])) {
			throw new Kdyby\InvalidStateException("Package '$packageName' is not registered.");
		}

		return $this->packages[$packageName];
	}



	/**
	 * @return \Kdyby\Package\ApplicationEventInvoker
	 */
	public function createInvoker()
	{
		return new ApplicationEventInvoker($this->getPackages());
	}



	/**
	 * @param \Kdyby\Package\IMetadataStorage $storage
	 */
	public function setMetadataStorage(IMetadataStorage $storage)
	{
		$this->metadataStorage = $storage;
	}



	/**
	 * @param string $packageName
	 * @return \Kdyby\Package\PackageMeta
	 */
	public function getPackageMeta($packageName)
	{
		return $this->metadataStorage->load($packageName);
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
		foreach ($this->getPackages() as $package) {
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
