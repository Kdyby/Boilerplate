<?php

namespace Kdyby\Validation;

use Kdyby;
use Nette;
use Doctrine;



class ValidationErrorsException extends \RuntimeException
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
	 * @param ErrorsException $errors
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
	 * @param ErrorsException $errors
	 * @return ErrorsException
	 */
	public static function notValid($entity, self $errors)
	{
		return new self("Entity " . get_class($entity) . " is not valid.", NULL, $errors);
	}

}