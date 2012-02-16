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
 *
 * @method mixed get() get(array $arr, $key, $default = NULL)
 * @method mixed getRef() getRef(array & $arr, $key)
 * @method array mergeTree() mergeTree(array $arr1, $arr2)
 * @method int searchKey() searchKey(array $arr, $key)
 * @method void insertBefore() insertBefore(array &$arr, $key, array $inserted)
 * @method void insertAfter() insertAfter(array &$arr, $key, array $inserted)
 * @method void renameKey() renameKey(array &$arr, $oldKey, $newKey)
 * @method array grep() grep(array $arr, $pattern, $flags = 0)
 */
final class Arrays extends Nette\Object
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
	 * @param array|string|callback $columns
	 * @param bool $append
	 *
	 * @return array
	 */
	public static function groupBy(array $array, $columns, $append = FALSE)
	{
		if (!is_callable($columns)) {
			$columns = is_array($columns)
				? $columns
				: Nette\Utils\Strings::split($columns, '~\s*,\s*~');
		}

		$grouped = array();
		foreach ($array as $item) {
			if (is_callable($columns)) {
				$keys = $columns($item);

			} else {
				$keys = array_map(function ($key) use ($item) {
					return is_object($item) ? $item->$key : $item[$key];
				}, $columns);
			}

			$ref =& Nette\Utils\Arrays::getRef($grouped, $keys);
			if ($append) {
				if (!is_array($ref)) {
					$ref = array();
				}
				$ref[] = $item;

			} else {
				$ref = $item;
			}
			unset($ref);
		}

		return $grouped;
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

		if ($callback === NULL) {
			return $items;
		}

		return array_map(callback($callback), $items);
	}



	/**
	 * @param array $array
	 * @param callable $callback
	 * @return array
	 */
	public static function flatFilter(array $array, $filter = NULL)
	{
		if ($filter === NULL) {
			return self::flatMap($array);
		}

		return array_filter(self::flatMap($array), callback($filter));
	}



	/**
	 * @param array|\Traversable $array
	 * @param callback $callback
	 * @return array
	 */
	public static function flatMapAssoc($array, $callback)
	{
		$callback = callback($callback);
		$result = array();
		$walker = function ($array, $keys = array()) use (&$walker, &$result, $callback) {
			foreach ($array as $key => $value) {
				$currentKeys = $keys + array(count($keys) => $key);
				if (is_array($value)) {
					$walker($value, $currentKeys);
					continue;
				}
				$result[] = $callback($value, $currentKeys);
			}

			return $result;
		};

		return $walker($array);
	}



	/**
	 * @param array $arr
	 * @param array $key
	 * @param callable $callback
	 * @return mixed
	 */
	public static function callOnRef(& $arr, $key, $callback)
	{
		if (!is_callable($callback, TRUE)) {
			throw new Kdyby\InvalidArgumentException("Invalid callback.");
		}

		return $callback(Nette\Utils\Arrays::getRef($arr, $key));
	}



	/**
	 * @param array $arr
	 * @param string $start
	 * @param string $end
	 * @return array
	 */
	public static function sliceAssoc(array $arr, $start, $end = NULL)
	{
		$sliced = array();
		foreach ($arr as $key => $value) {
			if ($key === $start || ($key === $end && $end !== NULL)) {
				if ($sliced) {
					$sliced[$key] = $value;
					break;
				}
				$sliced[$key] = $value;

			} elseif ($sliced) {
				$sliced[$key] = $value;
			}
		}
		return $sliced;
	}

}
