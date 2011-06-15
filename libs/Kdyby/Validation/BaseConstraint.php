<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
abstract class BaseConstraint extends Nette\Object implements IConstraint
{

	/**
	 * @param string $other
	 * @param string $description
	 * @param object|NULL $entity
	 * @param string|NULL $name
	 * @param boolean $not
	 * @throws Error
	 */
	public function fail($other, $description, $entity = NULL, $name = NULL, $not = FALSE)
	{
		throw new Error(
			$this->failureDescription($other, $description, $not),
			$entity,
			$name
		);
	}



	/**
	 * @param mixed $other
	 * @param string $description
	 * @param boolean $not
	 */
	protected function failureDescription($other, $description, $not)
	{
		$failureDescription = $this->customFailureDescription($other, $description, $not) ?: sprintf(
			'Failed asserting that %s %s.',
			Kdyby\Tools\Mixed::toString($other),
			$this->__toString()
		);

		if ($not) {
			$failureDescription = self::negate($failureDescription);
		}

		return $description . "\n" . $failureDescription;
	}



	/**
	 * @param mixed $other
	 * @param string $description
	 * @param boolean $not
	 */
	protected function customFailureDescription($other, $description, $not)
	{
		return NULL;
	}



	/**
	 * @param string $string
	 * @return string
	 */
	protected static function negate($string)
	{
		return str_replace(
			array(
				'contains ',
				'exists',
				'has ',
				'is ',
				'matches ',
				'starts with ',
				'ends with ',
				'not not '
			),
			array(
				'does not contain ',
				'does not exist',
				'does not have ',
				'is not ',
				'does not match ',
				'starts not with ',
				'ends not with ',
				'not '
			),
			$string
		);
	}

}