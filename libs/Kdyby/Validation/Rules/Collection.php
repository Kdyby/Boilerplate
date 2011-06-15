<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\Rules;

use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Kdyby;
use Kdyby\Validation;
use Nette;



/**
 * @author Filip Procházka
 */
final class Collection extends Nette\Object
{

	/** @var string */
	private $property;

	/** @var Validation\Rules */
	public $rules;

	/** @var array */
	public $on = array();



	/**
	 * @param string $property
	 * @param Validation\Rules $rules
	 */
	public function __construct($property, Validation\Rules $rules)
	{
		$this->property = $property;
		$this->rules = $rules;
	}



	/**
	 * @param Validation\IPropertyAccessor $propertyAccessor
	 * @return DoctrineCollection
	 */
	public function getCollection(Validation\IPropertyDecorator $decorator)
	{
		$propertyValue = $decorator->getValue($this->property);

		if (!$propertyValue instanceof DoctrineCollection) {
			throw new Nette\InvalidStateException("Property '" . $this->property . "' of '" . $decorator->getName() . "' does not contain 'Doctrine\\Common\\Collections\\Collection'.");
		}

		return $propertyValue;
	}

}