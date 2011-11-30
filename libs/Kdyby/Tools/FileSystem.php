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
final class FileSystem extends Nette\Object
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}



	/**
	 * @param string $directory
	 * @return boolean
	 */
	public static function rmDir($directory)
	{
		self::cleanDir($directory);
		return @rmdir($directory);
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
				@rmdir($file->getPathname());
			} else {
				@unlink($file->getPathname());
			}
		}
	}

}
