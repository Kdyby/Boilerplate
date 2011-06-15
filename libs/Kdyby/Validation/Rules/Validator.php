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
class Validator extends Nette\Object
{

	/** @var Validation\IConstraint */
	public $constraint;

	/** @var string|NULL */
	public $property;

	/** @var string */
	public $message;

	/** @var array */
	public $on = array();



	/**
	 * @param Validation\IConstraint $constraint
	 * @param string|NULL $property
	 * @param string $message
	 */
	public function __construct(Validation\IConstraint $constraint, $property, $message = NULL)
	{
		$this->constraint = $constraint;
		$this->property = $property;
		$this->message = $message;
	}



	/**
	 * @param string|array $event
	 * @return Validator
	 */
	public function on($event)
	{
		$events = is_array($event) ? $event : func_get_args();
		$this->on = array_filter($events, callback('is_string'));
		return $this;
	}

}