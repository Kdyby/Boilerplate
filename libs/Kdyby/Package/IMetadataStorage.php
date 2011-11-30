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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IMetadataStorage
{

	/**
	 * @param PackageMeta $meta
	 */
	function save(PackageMeta $meta);


	/**
	 * @param string $packageName
	 * @return PackageMeta
	 */
	function load($packageName);


	/**
	 * @param string $packageName
	 */
	function remove($packageName);

}