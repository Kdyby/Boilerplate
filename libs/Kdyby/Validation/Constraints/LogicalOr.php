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
class LogicalOr extends Validation\BaseConstraint
{

	/** @var Validation\IConstraint[] */
	protected $constraints = array();



	/**
	 * @param Validation\IConstraint[] $constraints
	 */
	public function setConstraints(array $constraints)
	{
		$this->constraints = array();

		foreach($constraints as $key => $constraint) {
			if (!$constraint instanceof Validation\IConstraint) {
				$constraint = new IsEqual($constraint);
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
			if ($constraint->evaluate($other)) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		$text = '';
		foreach($this->constraints as $key => $constraint) {
			$text .= ($key > 0 ? ' or ' : '') . $constraint->__toString();
		}

		return $text;
	}

}