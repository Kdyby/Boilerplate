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
	private $name;



	/**
	 * @param string $message
	 * @param object|NULL $invalidObject
	 * @param string|NULL $name
	 */
	public function __construct($message, $invalidObject = NULL, $name = NULL)
	{
		if (!is_string($message) || $message == "") {
			throw ExceptionFactory::invalidArgument(1, 'non-empty string', Mixed::getType($message));
		}

		if (!is_object($invalidObject) && $invalidObject !== NULL) {
			throw ExceptionFactory::invalidArgument(2, 'object', Mixed::getType($invalidObject));
		}

		if (!is_string($name) && $name !== NULL) {
			throw ExceptionFactory::invalidArgument(2, 'object', Mixed::getType($name));
		}

		parent::__construct($message);

		$this->invalidObject = $invalidObject;
		$this->name = $name;
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
	public function getName()
	{
		return $this->name;
	}

}