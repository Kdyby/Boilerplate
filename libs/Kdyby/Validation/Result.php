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
class Result extends \Exception implements Nette\IFreezable, \IteratorAggregate
{

	/** @var bool */
	private $frozen = FALSE;

	/** @var array */
	private $errors = array();



	/**
	 * No params
	 */
	public function __construct()
	{
		parent::__construct();
	}



	/**
	 * @param string $message
	 * @param string|NULL $name
	 * @param object|NULL $invalidObject
	 * @return Result
	 */
	public function addError($message, $name = NULL, $invalidObject = NULL)
	{
		$this->updating();
		$this->errors[] = new Error($message, $invalidObject, $name);
		return $this;
	}



	/**
	 * @param Result $result
	 * @return Result
	 */
	public function import(Result $result)
	{
		$this->updating();
		foreach ($result->getErrors() as $error) {
			$this->errors[] = $error;
		}

		return $this;
	}



	/**
	 * @return boolean
	 */
	public function isValid()
	{
		return !(bool)count($this->errors);
	}



	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}



	/************************ IteratorAggregate ************************/



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->getErrors());
	}



	/************************ IFreezable ************************/



	/**
	 * @return Result
	 */
	public function freeze()
	{
		$this->frozen = TRUE;
		return $this;
	}



	/**
	 * @return boolean
	 */
	public function isFrozen()
	{
		return $this->frozen;
	}



	/**
	 * @throws Nette\InvalidStateException
	 */
	protected function updating()
	{
		if ($this->frozen) {
			throw new Nette\InvalidStateException("Cannot modify frozen Result set.");
		}
	}

}