<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */

namespace Kdyby\Types;

use Nette;
use Kdyby;



/**
 * Adds suppor for extending
 * (can be used for packing huge repetitive data and lower change for misstakes)
 *
 * <code>
 * "__import" : {"key1":{"key2":[{"key":"value"}]}}
 * <code>
 * goes throught structure using keys, when used array,
 * the first item in list is condition to look for
 *
 * TODO:
 * <code>
 * "__import" : {"key1":{"key2":[{"key":"value"}, {"key3":[{"key":"value"}]}]}}
 * "__import" : {"key1":{"key2":[{"key":"value"}, [{"key":"value"}, [{"key":"value"}]]]}}
 * <code>,
 * the second item in list is the continuing map, that applies for found object
 * it shouldn't matter whether the second item is object or array
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Json extends Nette\Object
{

	const IMPORT_KEY = '__import';


	/** @var object */
	private $structure;



	/**
	 * @param object $structure
	 * @throws InvalidArgumentException
	 */
	public function __construct($structure)
	{
		if(!is_object($structure)) {
			throw new \InvalidArgumentException("Given structure must be object!");
		}

		$this->structure = $structure;
		$this->structure = $this->parse($this->structure);
	}



	/**
	 * @param string $file
	 * @throws IOException
	 * @return Json
	 */
	public static function fromFile($file)
	{
		if (!file_exists($file)) {
			throw new Nette\IOException("File '$file' not found");
		}

		return new static(Nette\Utils\Json::decode(file_get_contents($file)));
	}



	/**
	 * @param object $tree
	 * @throws Nette\Utils\JsonException
	 * @throws InvalidStateException
	 * @return object
	 */
	private function parse($branch)
	{
		if (is_array($branch)) {
			foreach ($branch as $index => $item) {
				$branch[$index] = $this->parse($item);
			}

		} elseif (is_object($branch)) {
			if (isset($branch->{self::IMPORT_KEY})) {
				$branch = $this->merge($this->find($branch->{self::IMPORT_KEY}), $branch);
				unset($branch->{self::IMPORT_KEY});
			}

			foreach ($branch as $key => $item) {
				$branch->{$key} = $this->parse($item);
			}
		}

		return $branch;
	}



	/**
	 * @param mixed $data
	 * @param mixed $branch
	 * @throws InvalidStateException
	 * @return mixed|NULL
	 */
	public function merge($data, $branch)
	{
		if (gettype($data) !== gettype($branch)) {
			throw new Nette\InvalidStateException("Data for merging doesn't have the same type as target branch");
		}

		if (is_array($data)) {
			return array_merge($data, $branch);

		} elseif (is_object($data)) {
			foreach ($data as $property => $value) {
				if (!isset($branch->{$property})) {
					$branch->{$property} = $value;
				}
			}
		}

		return $branch;
	}



	/**
	 * @param mixed $map
	 * @param mixed $branch
	 * @param integer $index
	 * @throws NotImplementedException
	 * @return mixed|NULL
	 */
	public function find($map, $branch = NULL, $index = 1)
	{
		$branch = $branch !== NULL ? $branch : $this->structure;

		if (is_object($map)) {
			if (!is_object($branch)) {
				throw new Nette\Utils\JsonException("Branch($index) type is not object, ".gettype($branch)." given.", $index);
			}

			$keys = array_keys(get_object_vars($map));
			$key = reset($keys);
			return $this->find($map->{$key}, $branch->{$key}, ++$index);

		} elseif(is_array($map)) {
			if (!is_array($branch)) {
				throw new Nette\Utils\JsonException("Branch($index) type is not array, ".gettype($branch)." given.", $index);
			}

			$conditions = reset($map);
			// $continuingMap = next($map);

			if (!is_object($conditions)) {
				throw new Nette\NotImplementedException("Branch($index) condition type ".gettype($conditions)." is not supported.", $index);
			}

			$match = NULL;
			foreach ($branch as $index => $item) {
				$match = $item;
				foreach ($conditions as $property => $value) {
					if ($item->{$property} !== $value) {
						$match = NULL;
						break;
					}
				}
				if ($match) {
					return $match;
				}
			}

		} else {
			return NULL;
		}

		return NULL;
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return $this->structure->{$name};
	}



	/**
	 * @return object
	 */
	public function export()
	{
		return $this->structure;
	}



	/**
	 * @return string
	 */
	public function encode()
	{
		return Nette\Utils\Json::encode($this->structure);
	}

}