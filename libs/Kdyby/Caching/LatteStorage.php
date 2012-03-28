<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Caching;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LatteStorage extends FileStorage
{

	/**
	 * @var string
	 */
	public $hint = '0';



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
		return parent::getCacheFile(substr_replace(
			$key,
			trim(strtr($this->hint, '\\/@', '.._'), '.') . '-',
			strpos($key, Nette\Caching\Cache::NAMESPACE_SEPARATOR) + 1,
			0
		)) . '.latte';
	}

}
