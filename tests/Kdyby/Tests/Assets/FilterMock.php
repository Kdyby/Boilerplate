<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Assets;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FilterMock extends Nette\Object implements Assetic\Filter\FilterInterface
{

	/**
	 * Filters an asset after it has been loaded.
	 *
	 * @param \Assetic\Asset\AssetInterface $asset An asset
	 */
	public function filterLoad(Assetic\Asset\AssetInterface $asset)
	{
	}



	/**
	 * Filters an asset just before it's dumped.
	 *
	 * @param \Assetic\Asset\AssetInterface $asset An asset
	 */
	public function filterDump(Assetic\Asset\AssetInterface $asset)
	{
	}

}
