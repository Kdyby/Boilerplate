<?php

namespace Kdyby\Validation;

use Kdyby;
use Nette;
use Doctrine;



class ErrorsException extends Nette\Object
{

	/** @var array */
	private $errors = array();



	/**
	 * @param array $error
	 */
	public function addError($error)
	{
		$this->errors[] = $error;
	}



	/**
	 * @return array
	 */
	public function hasErrors()
	{
		return (bool)$this->errors;
	}



	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}



	/**
	 * @param Kdyby\Validation\ErrorsException $errors
	 */
	public function import(self $errors)
	{
		foreach ($errors->getErrors() as $error) {
			$this->addError($error);
		}

		return $this;
	}



	/**
	 * @param array $entity
	 * @param Kdyby\Validation\ErrorsException $errors
	 * @return Kdyby\Validation\ErrorsException
	 */
	public static function notValid($entity, self $errors)
	{
		return new self("Entity " . get_class($entity) . " is not valid.", NULL, $errors);
	}

}