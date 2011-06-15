<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\Rules;

use Kdyby;
use Kdyby\Validation;
use Nette;



/**
 * @author Filip Procházka
 */
final class Condition extends Nette\Object
{

	/** @var Validation\IConstraint */
	public $constraint;

	/** @var string|NULL */
	public $property;

	/** @var Validation\Rules */
	public $rules;

	/** @var array */
	public $on = array();



	/**
	 * @param Validation\IConstraint $constraint
	 * @param Validation\Rules $rules
	 * @param string|NULL $property
	 */
	public function __construct(Validation\IConstraint $constraint, Validation\Rules $rules, $property = NULL)
	{
		$this->constraint = $constraint;
		$this->rules = $rules;
		$this->property = $property;
	}



	/**
	 * @param string|array $event
	 * @return Condition
	 */
	public function on($event)
	{
		$events = is_array($event) ? $event : func_get_args();
		$this->on = array_filter($events, callback('is_string'));
		return $this;
	}

}