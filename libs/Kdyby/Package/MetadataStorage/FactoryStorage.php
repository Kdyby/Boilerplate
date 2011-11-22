<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\MetadataStorage;

use Kdyby;
use Kdyby\Package\PackageMeta;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FactoryStorage extends Nette\Object implements Kdyby\Package\IMetadataStorage
{

	/** @var array */
	private $meta = array();



	/**
	 * @param PackageMeta $meta
	 */
	public function save(PackageMeta $meta)
	{
		$this->meta[strtolower($meta->getClass())] = $meta;
	}



	/**
	 * @param string $packageName
	 * @return PackageMeta
	 */
	public function load($packageName)
	{
		if (!class_exists($packageName)) {
			throw new Nette\InvalidArgumentException("Package '" . $packageName . "' not found.");
		}

		$lName = strtolower($packageName);
		if (!isset($this->meta[$lName])) {
			$package = new $packageName();
			$this->meta[$lName] = new PackageMeta($package);
		}

		return $this->meta[$lName];
	}



	/**
	 * @param string $packageName
	 */
	public function remove($packageName)
	{
		unset($this->meta[strtolower($packageName)]);
	}

}