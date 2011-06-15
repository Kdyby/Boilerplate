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
class TraversableContainsOnly extends Validation\BaseConstraint
{

	/** @var Validation\IConstraint */
	protected $constraint;

	/** @var string */
	protected $type;



	/**
	 * @param string $type
	 * @param boolean $isNativeType
	 */
	public function __construct($type, $isNativeType = TRUE)
	{
		if ($isNativeType) {
			$this->constraint = new IsType($type);

		} else {
			$this->constraint = new IsInstanceOf($type);
		}

		$this->type = $type;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		foreach ($other as $item) {
			if (!$this->constraint->evaluate($item)) {
				return FALSE;
			}
		}

		return TRUE;
	}



	/**
	 * @return TraversableContainsOnly
	 */
	public static function create($name, $param, $type, $isNative = TRUE)
	{
		return new static($type, $isNative);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'contains only values of type "' . $this->type . '"';
	}

}