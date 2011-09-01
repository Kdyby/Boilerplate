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
 * @author Filip Procházka
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
	 * @param string $path
	 * @param boolean $preserveSymlink
	 * @return string|FALSE
	 */
	public static function realPath($path, $preserveSymlink = FALSE)
	{
		if (!$preserveSymlink || !realpath($path)) {
			return realpath($path);
		}

		$absolutes = array();
		$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
		foreach(explode('/', $path) as $i => $fold){
			if ($fold == '' || $fold == '.') {
				continue;
			}

			if ($fold == '..' && $i > 0 && end($absolutes) != '..') {
				array_pop($absolutes);

			} else {
				$absolutes[] = $fold;
			}
		}

		return ($path[0] == DIRECTORY_SEPARATOR ? DIRECTORY_SEPARATOR : '') .
			implode(DIRECTORY_SEPARATOR, $absolutes);
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