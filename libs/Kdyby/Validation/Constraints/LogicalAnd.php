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
class LogicalAnd extends Validation\BaseConstraint
{

	/** @var Validation\IConstraint[] */
	protected $constraints = array();



	/**
	 * @param Validation\IConstraint[] $constraints
	 */
	public function __construct(array $constraints)
	{
		$this->constraints = array();

		foreach($constraints as $key => $constraint) {
			if (!$constraint instanceof Validation\IConstraint) {
				throw new Nette\InvalidArgumentException('All parameters to ' . __CLASS__ . ' must be a constraint object.');
			}

			$this->constraints[] = $constraint;
		}
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		foreach($this->constraints as $constraint) {
			if (!$constraint->evaluate($other)) {
				return FALSE;
			}
		}

		return TRUE;
	}



	/**
	 * @return LogicalAnd
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
		$text = '';
		foreach($this->constraints as $key => $constraint) {
			$text .= ($key > 0 ? ' and ' : '') . $constraint->__toString();
		}

		return $text;
	}

}