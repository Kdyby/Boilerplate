<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
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
	 * @param bool $need
	 *
	 * @return bool
	 * @throws \Kdyby\FileNotWritableException
	 */
	public static function rm($file, $need = TRUE)
	{
		if (is_dir((string)$file)) {
			return static::rmDir($file, FALSE, $need);
		}

		if (FALSE === ($result = @unlink((string)$file)) && $need) {
			throw new Kdyby\FileNotWritableException("Unable to delete file '$file'");
		}

		return $result;
	}



	/**
	 * @param string $dir
	 * @param bool $recursive
	 * @param bool $need
	 *
	 * @return boolean
	 * @throws \Kdyby\DirectoryNotWritableException
	 */
	public static function rmDir($dir, $recursive = TRUE, $need = TRUE)
	{
		$recursive && self::cleanDir($dir = (string)$dir, $need);
		if (is_dir($dir) && FALSE === ($result = @rmdir($dir)) && $need) {
			throw new Kdyby\DirectoryNotWritableException("Unable to delete directory '$dir'.");
		}

		return isset($result) ? $result : TRUE;
	}



	/**
	 * @param string $dir
	 * @param bool $need
	 *
	 * @return bool
	 */
	public static function cleanDir($dir, $need = TRUE)
	{
		if (!file_exists($dir)) {
			return TRUE;
		}

		foreach (Nette\Utils\Finder::find('*')->from($dir)->childFirst() as $file) {
			if (FALSE === static::rm($file, $need)) {
				return FALSE;
			}
		}

		return TRUE;
	}



	/**
	 * @param string $dir
	 * @param bool $recursive
	 * @param int $chmod
	 * @param bool $need
	 *
	 * @throws \Kdyby\IOException
	 */
	public static function mkDir($dir, $recursive = TRUE, $chmod = 0777, $need = TRUE)
	{
		$parentDir = $dir;
		while (!is_dir($parentDir)) {
			$parentDir = dirname($parentDir);
		}

		@umask(0000);
		if (!is_dir($dir) && FALSE === ($result = @mkdir($dir, $chmod, $recursive)) && $need) {
			throw new Kdyby\IOException('Unable to create directory ' . $dir);
		}

		if ($dir !== $parentDir) {
			do {
				@umask(0000);
				@chmod($dir, $chmod);
				$dir = dirname($dir);
			} while ($dir !== $parentDir);
		}

		return isset($result) ? $result : TRUE;
	}



	/**
	 * @param string $file
	 * @param string $contents
	 * @param bool $createDirectory
	 * @param int $chmod
	 * @param bool $need
	 *
	 * @return int
	 * @throws \Kdyby\FileNotWritableException
	 */
	public static function write($file, $contents, $createDirectory = TRUE, $chmod = 0777, $need = TRUE)
	{
		$createDirectory && static::mkDir(dirname($file), TRUE, $chmod);

		if (FALSE === ($result = @file_put_contents($file, $contents)) && $need) {
			throw Kdyby\FileNotWritableException::fromFile($file);
		}
		@chmod($file, $chmod);

		return $result;
	}



	/**
	 * @param string $file
	 * @param bool $need
	 *
	 * @return string
	 * @throws \Kdyby\FileNotFoundException
	 */
	public static function read($file, $need = TRUE)
	{
		if (FALSE === ($contents = @file_get_contents($file)) && $need) {
			throw new Kdyby\FileNotFoundException('File "' . $file . '" is not readable.');
		}

		return $contents;
	}

}
