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
class LogicalNot extends Validation\BaseConstraint
{

	/** @var Validation\IConstraint */
	protected $constraint;



	/**
	 * @param Validation\IConstraint $constraint
	 */
	public function __construct($constraint)
	{
		if (!$constraint instanceof Validation\IConstraint) {
			$constraint = new IsEqual($constraint);
		}

		$this->constraint = $constraint;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		return !$this->constraint->evaluate($other);
	}



	/**
	 * @param array $args
	 * @return LogicalNot
	 */
	public static function create($name, $property, $constraint)
	{
		return new static($constraint);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		if ($this->constraint instanceof LogicalAnd
				|| $this->constraint instanceof LogicalOr
				|| $this->constraint instanceof LogicalXor) {
			return 'not( ' . $this->constraint->__toString() . ' )';
		}

		return self::negate($this->constraint->__toString());
	}

}