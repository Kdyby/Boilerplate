<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Entities;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nette;
use Nette\Environment;
use Nette\ObjectMixin;
use Nette\Reflection\ClassType;
use Kdyby;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\MappedSuperclass()
 *
 * @property-read int $id
 */
abstract class BaseEntity extends Nette\Object implements \Serializable
{

	/**
	 * @var array
	 */
	private static $properties = array();

	/**
	 * @var array
	 */
	private static $methods = array();



	/**
	 */
	public function __construct() { }



	/**
	 * Allows the user to access through magic methods to protected and public properties.
	 * There are get<name>() and set<name>($value) methods for every protected or public property,
	 * and for protected or public collections there are add<name>($entity), remove<name>($entity) and has<name>($entity).
	 * When you'll try to call setter on collection, or collection manipulator on generic value, it will throw.
	 * Getters on collections will return all it's items.
	 *
	 * @param string $name method name
	 * @param array $args arguments
	 *
	 * @throws \Kdyby\UnexpectedValueException
	 * @throws \Kdyby\MemberAccessException
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (strlen($name) > 3) {
			$properties = $this->listObjectProperties();

			$op = substr($name, 0, 3);
			$prop = strtolower($name[3]) . substr($name, 4);
			if ($op === 'set' && isset($properties[$prop])) {
				if ($this->$prop instanceof Collection) {
					throw Kdyby\UnexpectedValueException::collectionCannotBeReplaced($this, $prop);
				}

				$this->$prop = $args[0];
				return $this;

			} elseif ($op === 'get' && isset($properties[$prop])) {
				if ($this->$prop instanceof Collection) {
					return $this->$prop->toArray();

				} else {
					return $this->$prop;
				}

			} else { // collections
				if ($op === 'add') {
					if (isset($properties[$prop . 's'])) {
						if (!$this->{$prop . 's'} instanceof Collection) {
							throw Kdyby\UnexpectedValueException::notACollection($this, $prop . 's');
						}

						$this->{$prop . 's'}->add($args[0]);
						return $this;

					} elseif (substr($prop, -1) === 'y' && isset($properties[$prop = substr($prop, 0, -1) . 'ies'])) {
						if (!$this->$prop instanceof Collection) {
							throw Kdyby\UnexpectedValueException::notACollection($this, $prop);
						}

						$this->$prop->add($args[0]);
						return $this;

					} elseif (isset($properties[$prop])) {
						throw Kdyby\UnexpectedValueException::notACollection($this, $prop);
					}

				} elseif ($op === 'has') {
					if (isset($properties[$prop . 's'])) {
						if (!$this->{$prop . 's'} instanceof Collection) {
							throw Kdyby\UnexpectedValueException::notACollection($this, $prop . 's');
						}

						return $this->{$prop . 's'}->contains($args[0]);

					} elseif (substr($prop, -1) === 'y' && isset($properties[$prop = substr($prop, 0, -1) . 'ies'])) {
						if (!$this->$prop instanceof Collection) {
							throw Kdyby\UnexpectedValueException::notACollection($this, $prop);
						}

						return $this->$prop->contains($args[0]);

					} elseif (isset($properties[$prop])) {
						throw Kdyby\UnexpectedValueException::notACollection($this, $prop);
					}

				} elseif (strlen($name) > 6 && ($op = substr($name, 0, 6)) === 'remove') {
					$prop = strtolower($name[6]) . substr($name, 7);

					if (isset($properties[$prop . 's'])) {
						if (!$this->{$prop . 's'} instanceof Collection) {
							throw Kdyby\UnexpectedValueException::notACollection($this, $prop . 's');
						}

						$this->{$prop . 's'}->removeElement($args[0]);
						return $this;

					} elseif (substr($prop, -1) === 'y' && isset($properties[$prop = substr($prop, 0, -1) . 'ies'])) {
						if (!$this->$prop instanceof Collection) {
							throw Kdyby\UnexpectedValueException::notACollection($this, $prop);
						}

						$this->$prop->removeElement($args[0]);
						return $this;

					} elseif (isset($properties[$prop])) {
						throw Kdyby\UnexpectedValueException::notACollection($this, $prop);
					}
				}
			}
		}

		if ($name === '') {
			throw Kdyby\MemberAccessException::callWithoutName($this);
		}

		// event functionality
		$class = new Nette\Reflection\ClassType($this);
		if ($class->hasEventProperty($name)) {
			if (is_array($list = $this->$name) || $list instanceof \Traversable) {
				foreach ($list as $handler) {
					callback($handler)->invokeArgs($args);
				}

			} elseif ($list !== NULL) {
				throw Kdyby\UnexpectedValueException::invalidEventValue($list, $this, $name);
			}

			return NULL;
		}

		// extension methods
		if ($cb = $class->getExtensionMethod($name)) {
			/** @var \Nette\Callback $cb */
			array_unshift($args, $this);
			return $cb->invokeArgs($args);
		}

		throw Kdyby\MemberAccessException::undefinedMethodCall($this, $name);
	}



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param string $name property name
	 *
	 * @throws \Kdyby\MemberAccessException if the property is not defined.
	 * @return mixed property value
	 */
	public function &__get($name)
	{
		if ($name === '') {
			throw Kdyby\MemberAccessException::propertyReadWithoutName($this);
		}

		// property getter support
		$name[0] = $name[0] & "\xDF"; // case-sensitive checking, capitalize first character
		$m = 'get' . $name;

		$methods = $this->listObjectMethods();
		if (isset($methods[$m])) {
			// ampersands:
			// - uses &__get() because declaration should be forward compatible (e.g. with Nette\Utils\Html)
			// - doesn't call &$_this->$m because user could bypass property setter by: $x = & $obj->property; $x = 'new value';
			$val = $this->$m();
			return $val;
		}

		$m = 'is' . $name;
		if (isset($methods[$m])) {
			$val = $this->$m();
			return $val;
		}

		// protected attribute support
		$properties = $this->listObjectProperties();
		if (isset($properties[$name = func_get_arg(0)])) {
			if ($this->$name instanceof Collection) {
				$coll = $this->$name->toArray();
				return $coll;

			} else {
				$val = $this->$name;
				return $val;
			}
		}

		$type = isset($methods['set' . $name]) ? 'a write-only' : 'an undeclared';
		throw Kdyby\MemberAccessException::propertyNotReadable($type, $this, func_get_arg(0));
	}



	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param string $name property name
	 * @param mixed $value property value
	 *
	 * @throws \Kdyby\MemberAccessException if the property is not defined or is read-only
	 */
	public function __set($name, $value)
	{
		if ($name === '') {
			throw Kdyby\MemberAccessException::propertyWriteWithoutName($this);
		}

		// property setter support
		$name[0] = $name[0] & "\xDF"; // case-sensitive checking, capitalize first character

		$methods = $this->listObjectMethods();
		$m = 'set' . $name;
		if (isset($methods[$m])) {
			$this->$m($value);
			return;
		}

		// protected attribute support
		$properties = $this->listObjectProperties();
		if (isset($properties[$name = func_get_arg(0)])) {
			if ($this->$name instanceof Collection) {
				throw Kdyby\UnexpectedValueException::collectionCannotBeReplaced($this, $name);
			}

			$this->$name = $value;
			return;
		}

		$type = isset($methods['get' . $name]) || isset($methods['is' . $name]) ? 'a read-only' : 'an undeclared';
		throw Kdyby\MemberAccessException::propertyNotWritable($type, $this, func_get_arg(0));
	}



	/**
	 * Is property defined?
	 *
	 * @param string $name property name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		$properties = $this->listObjectProperties();
		if (isset($properties[$name])) {
			return TRUE;
		}

		if ($name === '') {
			return FALSE;
		}

		$methods = $this->listObjectMethods();
		$name[0] = $name[0] & "\xDF";
		return isset($methods['get' . $name]) || isset($methods['is' . $name]);
	}



	/**
	 * Should return only public or protected properties of class
	 *
	 * @return array
	 */
	private function listObjectProperties()
	{
		$class = get_class($this);
		if (!isset(self::$properties[$class])) {
			self::$properties[$class] = array_flip(array_keys(get_object_vars($this)));
		}

		return self::$properties[$class];
	}



	/**
	 * Should return all public methods of class
	 *
	 * @return array
	 */
	private function listObjectMethods()
	{
		$class = get_class($this);
		if (!isset(self::$methods[$class])) {
			// get_class_methods returns ONLY PUBLIC methods of objects
			// but returns static methods too (nothing doing...)
			// and is much faster than reflection
			// (works good since 5.0.4)
			self::$methods[$class] = array_flip(get_class_methods($class));
		}

		return self::$methods[$class];
	}



	/**************************** \Serializable ****************************/



	/**
	 * @internal
	 * @return string
	 */
	public function serialize()
	{
		$data = array();

		$allowed = FALSE;
		if (method_exists($this, '__sleep')) {
			$allowed = (array)$this->__sleep();
		}

		$class = $this->getReflection();

		do {
			/** @var \Nette\Reflection\Property $propertyRefl */
			foreach ($class->getProperties() as $propertyRefl) {
				if ($allowed !== FALSE && !in_array($propertyRefl->getName(), $allowed)) {
					continue;

				} elseif ($propertyRefl->isStatic()) {
					continue;
				}

				// prefix private properties
				$prefix = $propertyRefl->isPrivate() ? $propertyRefl->getDeclaringClass()->getName() . '::' : NULL;

				// save value
				$propertyRefl->setAccessible(TRUE);
				$data[$prefix . $propertyRefl->getName()] = $propertyRefl->getValue($this);
			}

		} while ($class = $class->getParentClass());

		bd($data, get_called_class() . '->' . __FUNCTION__ . '()');
		return serialize($data);
	}



	/**
	 * @internal
	 *
	 * @param $serialized
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);

		foreach ($data as $target => $value) {
			if (strpos($target, '::') !== FALSE) {
				list($class, $name) = explode('::', $target, 2);
				$propertyRefl = static::getProperty($name, $class);

			} else {
				$propertyRefl = static::getProperty($target);
			}

			$propertyRefl->setAccessible(TRUE);
			$propertyRefl->setValue($this, $value);
		}

		if (method_exists($this, '__wakeup')) {
			$this->__wakeup();
		}
	}



	/**
	 * @var array|\Nette\Reflection\ClassType[]
	 */
	private static $classes = array();



	/**
	 * @param string $name
	 * @param string $class
	 * @return \Nette\Reflection\Property
	 */
	private static function getProperty($name, $class = NULL)
	{
		if (isset(self::$classes[$class])) {
			$class = self::$classes[$class];

		} else {
			if ($class === NULL) {
				$class = static::getReflection();

			} else {
				$class = ClassType::from($class);
			}

			self::$classes[func_get_arg(1)] = $class;
		}

		return $class->getProperty($name);
	}

}
