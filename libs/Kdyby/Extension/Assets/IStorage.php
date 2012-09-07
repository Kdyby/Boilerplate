<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Assets;

use Assetic;
use Assetic\Asset\AssetInterface;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface IStorage
{

	/**
	 * @param \Assetic\AssetManager $am
	 */
	function writeManagerAssets(Assetic\AssetManager $am);

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 */
    function writeAsset(AssetInterface $asset);

	/**
	 * @param string|\Assetic\Asset\AssetInterface $asset
	 *
	 * @return string
	 */
	function getAssetUrl($asset);

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 *
	 * @return bool
	 */
	function isFresh(AssetInterface $asset);

}
