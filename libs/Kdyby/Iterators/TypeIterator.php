<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Iterators;

use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TypeIterator extends SelectIterator
{

	/** @var array */
	private $types = array();



	/**
	 * @param array|\Iterator $types
	 *
	 * @return TypeIterator
	 */
	public static function from($types)
	{
		if (!$types instanceof \Iterator) {
			$types = new \ArrayIterator($types);
		}

		return new static($types);
	}



	/**
	 * @return TypeIterator
	 */
	public static function fromDeclared()
	{
		return static::from(get_declared_classes());
	}



	/**
	 * @return TypeIterator
	 */
	public function isAbstract()
	{
		return $this->select(function (TypeIterator $iterator)
		{
			return $iterator->current()->isAbstract();
		});
	}



	/**
	 * @return TypeIterator
	 */
	public function isSubclassOf($class)
	{
		return $this->select(function (TypeIterator $iterator) use ($class)
		{
			if ($iterator->current()->isInterface()) {
				return FALSE;
			}

			return $iterator->current()->isSubclassOf($class);
		});
	}



	/**
	 * @param string $interface
	 *
	 * @return TypeIterator
	 */
	public function implementsInterface($interface)
	{
		return $this->select(function (TypeIterator $iterator) use ($interface)
		{
			return $iterator->current()->implementsInterface($interface);
		});
	}



	/**
	 * @return TypeIterator
	 */
	public function isInstantiable()
	{
		return $this->select(function (TypeIterator $iterator)
		{
			return $iterator->current()->isInstantiable();
		});
	}



	/**
	 * @param $namespace
	 *
	 * @return TypeIterator
	 */
	public function inNamespace($namespace)
	{
		return $this->select(function (TypeIterator $iterator) use ($namespace)
		{
			$ns = $iterator->current()->getNamespaceName();
			return substr($ns, 0, strlen($namespace)) === $namespace;
		});
	}



	/**
	 * @return ClassType
	 */
	public function current()
	{
		$type = parent::current();

		if (!isset($this->types[$type])) {
			$this->types[$type] = ClassType::from($type);
		}

		return $this->types[$type];
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return array_map(function (ClassType $type)
		{
			return $type->getName();
		}, parent::toArray());
	}



	/**
	 * @return array
	 */
	public function getResult()
	{
		return $this->toArray();
	}

}
