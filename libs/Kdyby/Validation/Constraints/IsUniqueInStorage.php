<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\Constraints;

use Kdyby;
use Kdyby\Validation;
use Nette;



/**
 * @author Filip Procházka
 */
class IsUniqueInStorage extends Validation\BaseConstraint
{

	/** @var string */
	protected $attributeName;

	/** @var Validation\IStorage */
	protected $storage;



	/**
	 * @param string $attributeName
	 * @param Validation\IStorage $storage
	 */
	public function __construct($attributeName, Validation\IStorage $storage)
	{
		$this->attributeName = $attributeName;
		$this->storage = $storage;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return $this->storage->countByAttribute($this->attributeName, $other) == 0;
	}



	/**
	 * @param array $args
	 * @return IsUniqueInStorage
	 */
	public static function create($name, $property, $storage)
	{
		return new static($property, $storage);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'is unique in storage by attribute "' . $this->attributeName . '"';
	}

}