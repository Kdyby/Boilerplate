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
class IsType extends Validation\BaseConstraint
{

	const TYPE_ARRAY = 'array';
	const TYPE_BOOL = 'bool';
	const TYPE_FLOAT = 'float';
	const TYPE_INT = 'int';
	const TYPE_NULL = 'null';
	const TYPE_NUMERIC = 'numeric';
	const TYPE_OBJECT = 'object';
	const TYPE_RESOURCE = 'resource';
	const TYPE_STRING = 'string';
	const TYPE_SCALAR = 'scalar';

	/** @var array */
	protected $types = array(
	  'array' => TRUE,
	  'boolean' => TRUE,
	  'bool' => TRUE,
	  'float' => TRUE,
	  'double' => TRUE,
	  'integer' => TRUE,
	  'int' => TRUE,
	  'null' => TRUE,
	  'numeric' => TRUE,
	  'object' => TRUE,
	  'resource' => TRUE,
	  'string' => TRUE,
	  'scalar' => TRUE
	);

	/** @var string */
	protected $type;



	/**
	 * @param string $type
	 * @throws Nette\InvalidArgumentException
	 */
	public function __construct($type)
	{
		$type = strtolower($type);

		if (!isset($this->types[$type])) {
			throw new Nette\InvalidArgumentException("Given type '$type' is now known.");
		}

		$this->type = $type;
	}



	/**
	 * @param mixed $other
	 * @return bool
	 */
	public function evaluate($other)
	{
		switch ($this->type) {
			case 'numeric':
				return is_numeric($other);

			case 'integer':
			case 'int':
				return is_integer($other);

			case 'float':
				return is_float($other);

			case 'double':
				return is_double($other);

			case 'string':
				return is_string($other);

			case 'boolean':
			case 'bool':
				return is_bool($other);

			case 'null':
				return is_null($other);

			case 'array':
				return is_array($other);

			case 'object':
				return is_object($other);

			case 'resource':
				return is_resource($other);

			case 'scalar':
				return is_scalar($other);
		}
	}



	/**
	 * @return IsType
	 */
	public static function create($name, $property, $type = NULL)
	{
		$name = strtolower($name);
		return new static($name != 'istype' && $name != 'type' ? $name : $type);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'is of type "' . $this->type . '"';
	}

}