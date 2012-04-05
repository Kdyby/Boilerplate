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
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Nette\Utils\Neon;



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
	 * @return string
	 */
	public function getFilename()
	{
		if (is_file($default = $this->appDir . '/config/packages.neon')) {
			return $default;

		} elseif (is_file($file = $this->appDir . '/packages.neon')) {
			return $file;
		}

		return $default;
	}



	/**
	 * @return array
	 */
	public function getPackages()
	{
		if (!file_exists($file = $this->getFilename())) {
			$list = $this->supplyDefaultPackages($file);

		} else {
			try {
				$list = (array)Neon::decode(@file_get_contents($file));

			} catch (Nette\Utils\NeonException $e) {
				throw new Kdyby\InvalidStateException("Packages file '$file' is corrupted!", NULL, $e);
			}
		}

		if (!$list) {
			throw new Kdyby\InvalidStateException("File '$file' is corrupted! Fix the file, or delete it.");
		}

		return $list;
	}



	/**
	 * @param string $file
	 *
	 * @throws \Kdyby\FileNotWritableException
	 * @return array
	 */
	private function supplyDefaultPackages($file)
	{
		if (class_exists('Kdyby\CMS')) {
			$default = Kdyby\CMS::createPackagesList()->getPackages();

		} else {
			$default = Kdyby\Framework::getDefaultPackages();
		}

		if (!@file_put_contents($file, Neon::encode($default, Neon::BLOCK))) {
			throw Kdyby\FileNotWritableException::fromFile($file);
		}
		@chmod($file, 0777);
		return $default;
	}

}
