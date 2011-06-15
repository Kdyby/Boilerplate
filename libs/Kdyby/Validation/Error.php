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
use Kdyby\Tools\ExceptionFactory;
use Kdyby\Tools\Mixed;
use Nette;



/**
 * @author Filip Procházka
 */
class Error extends \Exception
{

	/** @var object */
	private $invalidObject;

	/** @var string */
	private $propertyName;



	/**
	 * @param string $message
	 * @param object|NULL $invalidObject
	 * @param string|NULL $propertyName
	 */
	public function __construct($message, $invalidObject = NULL, $propertyName = NULL)
	{
		if (!is_string($message) || $message == "") {
			throw ExceptionFactory::invalidArgument(1, 'non-empty string', Mixed::getType($message));
		}

		if (!is_object($invalidObject) && $invalidObject !== NULL) {
			throw ExceptionFactory::invalidArgument(2, 'object', Mixed::getType($invalidObject));
		}

		if (!is_string($propertyName) && $propertyName !== NULL) {
			throw ExceptionFactory::invalidArgument(2, 'string', Mixed::getType($propertyName));
		}

		parent::__construct($message);

		$this->invalidObject = $invalidObject;
		$this->propertyName = $propertyName;
	}



	/**
	 * @return object|NULL
	 */
	public function getInvalidObject()
	{
		return $this->invalidObject;
	}



	/**
	 * @return string|NULL
	 */
	public function getPropertyName()
	{
		return $this->propertyName;
	}

}