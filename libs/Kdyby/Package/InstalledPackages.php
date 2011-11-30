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
			throw new Nette\InvalidArgumentException("Please provide an application directory %appDir%.");
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
				return array();
			}

			$list = (array)Json::decode($file);
			return array_map(function ($package) { return $package->class; }, $list);

		} catch (JsonException $e) {
			throw new Nette\InvalidStateException("Packages file '$file' is corrupted!", NULL, $e);
		}
	}

}