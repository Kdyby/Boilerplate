<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
final class Filesystem extends Nette\Object
{

	/**
	 * Static class - cannot be instantiated.
	 *
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new Kdyby\StaticClassException;
	}



	/**
	 * @param string $file
	 */
	public static function rm($file)
	{
		@unlink((string)$file);
	}



	/**
	 * @param string $directory
	 * @param bool $recursive
	 *
	 * @return boolean
	 */
	public static function rmDir($directory, $recursive = TRUE)
	{
		$recursive && self::cleanDir((string)$directory);
		return @rmdir((string)$directory);
	}



	/**
	 * @param string $directory
	 */
	public static function cleanDir($directory)
	{
		if (!file_exists($directory)) {
			return;
		}

		foreach (Nette\Utils\Finder::find('*')->from($directory)->childFirst() as $file) {
			if ($file->isDir()) {
				static::rmDir($file, FALSE);

			} else {
				static::rm($file);
			}
		}
	}

}
