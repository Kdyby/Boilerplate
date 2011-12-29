<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IWriter
{

	/**
	 * @param \Assetic\AssetManager $am
	 */
	function writeManagerAssets(Assetic\AssetManager $am);

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 */
    function writeAsset(Assetic\Asset\AssetInterface $asset);

	/**
	 * @param string $assetOutput
	 * @param integer $unixtime
	 * @return bool
	 */
	function isFresh($assetOutput, $unixtime);

}
