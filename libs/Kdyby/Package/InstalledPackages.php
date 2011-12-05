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
use Kdyby\Tools\Json;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Nette\Utils\JsonException;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class InstalledPackages extends Nette\Object implements \IteratorAggregate, IPackageList
{

	/** @var string */
	private $appDir;



	/**
	 * @param string $appDir
	 */
	public function __construct($appDir)
	{
		if (!is_dir($appDir)) {
			throw new Kdyby\InvalidArgumentException("Please provide an application directory %appDir%.");
		}

		$this->appDir = $appDir;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getPackages());
	}



	/**
	 * @return array
	 */
	public function getPackages()
	{
		$file = $this->appDir . '/config/installed-packages.json';
		try {
			if (!file_exists($file)) {
				$list = $this->supplyDefaultPackages($file);

			} else {
				$list = (array)Json::decode($file);
			}

			$list = array_map(function ($package) {
				return $package->class;
			}, $list);

			if (!$list) {
				throw new Kdyby\InvalidStateException("File '$file' is corrupted! Fix the file, or delete it.");
			}

			return $list;

		} catch (JsonException $e) {
			throw new Kdyby\InvalidStateException("Packages file '$file' is corrupted!", NULL, $e);
		}
	}



	/**
	 * @param $file
	 */
	private function supplyDefaultPackages($file)
	{
		$default = array();
		if (class_exists('Kdyby\Packages\DefaultPackages')) {
			$packages = new \Kdyby\Packages\DefaultPackages();
			$default = $packages->getPackages();
		}
		if (class_exists('Kdyby\Packages\CmsPackages')) {
			$packages = new \Kdyby\Packages\CmsPackages();
			$default = array_merge($default, $packages->getPackages());
		}

		$list = array_map(function ($package) {
			return (object)array('class' => $package);
		}, $default->getPackages());

		if (!@file_put_contents($file, Json::encode($list))) {
			throw Kdyby\FileNotWritableException::fromFile($file);
		}
		@chmod($file, 0777);

		return $list;
	}

}
