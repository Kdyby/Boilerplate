<?php

/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */
/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Config;

use Kdyby;
use Nette;
use Nette\Debug;
use Nette\ArrayTools;



/**
 * Reading and writing NEON files.
 *
 * @author	Patrik Votoček
 * @author	David Grudl
 * @package	Nella\Config
 */
final class ConfigAdapterNeon implements \Nette\Config\IConfigAdapter
{
	/** @var string  section inheriting separator (section < parent) */
	public static $sectionSeparator = ' < ';



	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Reads configuration from NEON file.
	 * @param  string  file name
	 * @param  string  section to load
	 * @return array
	 * @throws \InvalidStateException
	 */
	public static function load($file, $section = NULL)
	{
		if (!is_file($file) || !is_readable($file)) {
			throw new \FileNotFoundException("File '$file' is missing or is not readable.");
		}

		Debug::tryError();
		$parser = new \Nette\NeonParser;
		$neon = $parser->parse(file_get_contents($file));
		if (Debug::catchError($e)) {
			throw $e;
		}

		$separator = trim(self::$sectionSeparator);
		$data = array();
		foreach ($neon as $secName => $secData) {
			// is section?
			if (is_array($secData)) {
				// process extends sections like staging < production)
				$parts = $separator ? explode($separator, strtr($secName, ':', $separator)) : array($secName);
				if (count($parts) > 1) {
					$parent = trim($parts[1]);
					$cursor = & $data;
					foreach (array($parent) as $part) {
						if (isset($cursor[$part]) && is_array($cursor[$part])) {
							$cursor = & $cursor[$part];
						} else {
							throw new \InvalidStateException("Missing parent section [$parent] in '$file'.");
						}
					}
					$secData = ArrayTools::mergeTree($secData, $cursor);
				}

				$secName = trim($parts[0]);
				if ($secName === '') {
					throw new \InvalidStateException("Invalid empty section name in '$file'.");
				}
			}

			$cursor = & $data[$secName];

			if (is_array($secData) && is_array($cursor)) {
				$secData = ArrayTools::mergeTree($secData, $cursor);
			}

			$cursor = $secData;
		}

		if ($section === NULL) {
			return $data;

		} elseif (!isset($data[$section]) || !is_array($data[$section])) {
			throw new \InvalidStateException("There is not section [$section] in '$file'.");

		} else {
			return $data[$section];
		}
	}



	/**
	 * Write NEON file.
	 * @param  Nette\Config\Config to save
	 * @param  string  file
	 * @param  string  section name
	 * @return void
	 */
	public static function save($config, $file, $section = NULL)
	{
		throw new \NotImplementedException;
	}



	/**
	 * Recursive builds NEON list.
	 * @param  array|\Traversable
	 * @param  array
	 * @param  string
	 * @return void
	 */
	private static function build($input, & $output, $prefix)
	{
		foreach ($input as $key => $val) {
			if (is_array($val) || $val instanceof \Traversable) {
				self::build($val, $output, $prefix . $key . self::$keySeparator);

			} elseif (is_bool($val)) {
				$output[] = "$prefix$key = " . ($val ? 'TRUE' : 'FALSE');

			} elseif (is_numeric($val)) {
				$output[] = "$prefix$key = $val";

			} elseif (is_string($val)) {
				$output[] = "$prefix$key = \"$val\"";

			} else {
				throw new \InvalidArgumentException("The '$prefix$key' item must be scalar or array, " . gettype($val) ." given.");
			}
		}
	}

}