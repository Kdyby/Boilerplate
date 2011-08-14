<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Iterators;

use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * @author Filip Procházka
 */
class TypeIterator extends SelectIterator
{

	/** @var array */
	private $types = array();



	/**
	 * @return TypeIterator
	 */
	public function isAbstract()
	{
		$this->select(function (TypeIterator $iterator) {
			return $iterator->current()->isAbstract();
		});

		return $this;
	}



	/**
	 * @return TypeIterator
	 */
	public function isSubclassOf($class)
	{
		$this->select(function (TypeIterator $iterator) use ($class) {
			if ($iterator->current()->isInterface()) {
				return FALSE;
			}

			return $iterator->current()->isSubclassOf($class);
		});

		return $this;
	}



	/**
	 * @param string $interface
	 * @return TypeIterator
	 */
	public function implementsInterface($interface)
	{
		$this->select(function (TypeIterator $iterator) use ($interface) {
			return $iterator->current()->implementsInterface($interface);
		});

		return $this;
	}



	/**
	 * @return TypeIterator
	 */
	public function isInstantiable()
	{
		$this->select(function (TypeIterator $iterator) {
			return $iterator->current()->isInstantiable();
		});

		return $this;
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
	public function getResult()
	{
		return array_map(function (ClassType $type) {
			return $type->getName();
		}, $this->toArray());
	}

}