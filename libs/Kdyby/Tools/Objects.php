<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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
	 * Expands 'path.to.property' in string.
	 *
	 * @param string|array $path
	 * @param object|array $entity
	 * @param boolean $need
	 *
	 * @return mixed
	 */
	public static function expand($path, $entity, $need = TRUE)
	{
		$value = $entity;
		foreach (is_array($path) ? $path : explode('.', $path) as $part) {
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
	 *
	 * @return mixed|NULL
	 */
	public static function getProperty($object, $propertyName, $need = TRUE)
	{
		if (is_array($object) || $object instanceof \ArrayAccess || $object instanceof \ArrayObject) {
			return $object[$propertyName];

		} elseif (is_object($object)) {
			if (method_exists($object, $method = 'get' . ucfirst($propertyName))) {
				return $object->$method();

			} elseif (property_exists($object, $propertyName)) {
				return $object->$propertyName;

			} elseif (method_exists($object, $method = 'is' . ucfirst($propertyName))) {
				return $object->$method();
			}
		}

		if ($need) {
			throw new Kdyby\MemberAccessException("Given" . (is_object($object) ? " entity " . get_class($object) : " array") . " has no public parameter or accesor named '" . $propertyName . "', or doesn't exists.");
		}
	}



	/**
	 * @param object $object
	 * @param array $options
	 * @param boolean $exceptionOnInvalid
	 *
	 * @throws \Kdyby\InvalidArgumentException
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
		if (property_exists($object, $propertyName)) {
			$object->$propertyName = $value;

		} elseif (method_exists($object, $method = "set" . ucfirst($propertyName))) {
			$object->$method($value);

		} elseif (method_exists($object, $method = "add" . ucfirst($propertyName))) {
			$object->$method($value);

		} elseif ($exceptionOnInvalid) {
			throw new Kdyby\MemberAccessException("Property with name '$propertyName' is not publicly writable, or doesn't exists.");
		}
	}

}
