<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;

use Kdyby;
use Kdyby\Extension\Assets\Repository;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IAssetRepository
{

	/**
	 * @param string $name
	 * @param string $version
	 *
	 * @return boolean
	 */
	function hasAsset($name, $version = NULL);


	/**
	 * @param string $name
	 * @param string $version
	 *
	 * @return \Kdyby\Extension\Assets\Repository\AssetPackage
	 */
	function getAsset($name, $version = NULL);


	/**
	 * @param \Kdyby\Extension\Assets\Repository\AssetPackage $asset
	 */
	function registerAsset(Repository\AssetPackage $asset);

}
