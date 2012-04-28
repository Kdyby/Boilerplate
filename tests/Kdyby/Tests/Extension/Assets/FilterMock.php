<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Assets;

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
