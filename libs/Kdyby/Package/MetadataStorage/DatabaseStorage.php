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
class DatabaseStorage extends Nette\Object implements Kdyby\Package\IMetadataStorage
{





	/**
	 * @param PackageMeta $meta
	 */
	public function save(PackageMeta $meta)
	{
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
	}


	/**
	 * @param string $packageName
	 */
	public function remove($packageName)
	{

	}

}