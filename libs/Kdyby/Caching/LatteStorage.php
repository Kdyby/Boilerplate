<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Caching;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LatteStorage extends FileStorage
{

	/**
	 * Reads cache data from disk.
	 *
	 * @param array $meta
	 *
	 * @return mixed
	 */
	protected function readData($meta)
	{
		return array(
			'file' => $meta[self::FILE],
			'handle' => $meta[self::HANDLE],
		);
	}



	/**
	 * Returns file name.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	protected function getCacheFile($key)
	{
		return parent::getCacheFile($key) . '.latte';
	}

}
