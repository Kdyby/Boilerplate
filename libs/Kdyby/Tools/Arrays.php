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
 *
 * @method mixed get() get(array $arr, $key, $default = NULL)
 * @method mixed getRef() getRef(& $arr, $key)
 * @method array mergeTree() mergeTree($arr1, $arr2)
 * @method int searchKey() searchKey($arr, $key)
 * @method void insertBefore() insertBefore(array &$arr, $key, array $inserted)
 * @method void insertAfter() insertAfter(array &$arr, $key, array $inserted)
 * @method void renameKey() renameKey(array &$arr, $oldKey, $newKey)
 * @method array grep() grep(array $arr, $pattern, $flags = 0)
 */
final class Arrays extends Nette\Object
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}



	/**
	 * Mirror of Nette\Utils\Arrays
	 *
	 * @param string $name
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		return callback('Nette\Utils\Arrays', $name)->invokeArgs($args);
	}



	/**
	 * @param array $array
	 * @param callable $callback
	 * @return array
	 */
	public static function flatMap(array $array, $callback = NULL)
	{
		$items = array();
		array_walk_recursive($array, function ($item, $key) use (&$items) {
			$items[] = $item;
		});

		if ($callback) {
			return array_map($callback, $items);
		}

		return $items;
	}

}