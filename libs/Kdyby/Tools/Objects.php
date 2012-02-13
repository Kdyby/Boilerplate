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
final class Objects extends Nette\Object
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
	 * Expands %placeholders% in string.
	 * @param string $path
	 * @param object|array $entity
	 * @param boolean $need
	 * @throws Kdyby\InvalidArgumentException
	 * @return mixed
	 */
	public static function expand($path, $entity, $need = TRUE)
	{
		$value = $entity;
		foreach (explode('.', $path) as $n => $part) {
			$e = get_class($value) . '::' . $part;
			$value = self::getProperty($value, $part, $need);
			if ($value === NULL) {
				break;
			}
		}
		return $value;
	}



	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param bool $need
	 * @return mixed|NULL
	 */
	public static function getProperty($object, $propertyName, $need = TRUE)
	{
		if (is_object($object)) {
			if (isset($object->$propertyName)) {
				return $object->$propertyName;

			} elseif (method_exists($object, $method = 'get' . ucfirst($propertyName))) {
				return $object->$method();

			} elseif (method_exists($object, $method = 'is' . ucfirst($propertyName))) {
				return $object->$method();
			}

		} elseif (is_array($object) || $object instanceof \ArrayAccess || $object instanceof \ArrayObject) {
			return $object[$propertyName];
		}

		if ($need) {
			throw new Kdyby\InvalidStateException("Given" . (is_object($object) ? " entity " . get_class($object) : " array") . " has no parameter named '" . $propertyName . "'.");
		}
	}



	/**
	 * @param object $object
	 * @param array $options
	 * @param boolean $exceptionOnInvalid
	 * @throws Kdyby\InvalidArgumentException
	 */
	public static function setProperties($object, array $options, $exceptionOnInvalid = TRUE)
	{
		if (!is_object($object)) {
			throw new Kdyby\InvalidArgumentException("Can by applied only to objects.");
		}

		foreach	($options as $name => $value) {
			self::setProperty($object, $name, $value, $exceptionOnInvalid);
		}
	}



	/**
	 * @param object $object
	 * @param string $propertyName
	 * @param mixed $value
	 * @param boolean $exceptionOnInvalid
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public static function setProperty($object, $propertyName, $value, $exceptionOnInvalid = TRUE)
	{
		if (isset($object->$propertyName)) {
			$object->$propertyName = $value;

		} elseif (method_exists($object, $method = "set" . ucfirst($propertyName))) {
			$object->$method($value);

		} elseif (method_exists($object, $method = "add" . ucfirst($propertyName))) {
			$object->$method($value);

		} elseif ($exceptionOnInvalid) {
			throw new Kdyby\InvalidArgumentException("Option with name $propertyName does not exist.");
		}
	}

}
