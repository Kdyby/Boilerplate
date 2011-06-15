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
final class Relation extends Nette\Object
{

	/** @var string */
	private $property;

	/** @var Validation\Rules */
	public $rules;



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
	 * @return array
	 */
	public function getRelated(Validation\IPropertyDecorator $decorator)
	{
		$propertyValue = $decorator->getValue($this->property);

		if ($propertyValue instanceof DoctrineCollection) {
			return (array)$propertyValue->toArray();

		} elseif (is_array($propertyValue) || $propertyValue instanceof \Traversable) {
			return (array)$propertyValue;
		}

		return array($propertyValue);
	}

}