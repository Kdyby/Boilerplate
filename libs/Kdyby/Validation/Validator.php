<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Validator extends Nette\Object implements IValidator
{

	/** @var string */
	const CONSTRAINT_NS = 'Kdyby\Validation\Constraints';

	/** @var array */
	private static $constraintsMap = array(
		'greaterthan' => 'GreaterThan',
		'>' => 'GreaterThan',
		'lessthan' => 'LessThan',
		'<' => 'LessThan',

		'empty' => 'IsEmpty',
		'anything' => 'IsAnything',
		'*' => 'IsAnything',
		'equal' => 'IsEqual',
		'==' => 'IsEqual',
		'identical' => 'IsIdentical',
		'===' => 'IsIdentical',

		'type' => 'IsType',
		'instanceof' => 'IsInstanceOf',

		'null' => 'IsNull',
		'array' => 'IsType',
		'boolean' => 'IsType',
		'bool' => 'IsType',
		'float' => 'IsType',
		'double' => 'IsType',
		'integer' => 'IsType',
		'int' => 'IsType',
		'numeric' => 'IsType',
		'object' => 'IsType',
		'resource' => 'IsType',
		'string' => 'IsType',
		'scalar' => 'IsType',

		'false' => 'IsFalse',
		'true' => 'IsTrue',
//		'not' => 'LogicalNot',
//		'!' => 'LogicalNot',
//		'and' => 'LogicalAnd',
//		'&&' => 'LogicalAnd',
//		'or' => 'LogicalOr',
//		'||' => 'LogicalOr',
//		'xor' => 'LogicalXor',

		'matches' => 'StringMatches',
		'matchespattern' => 'MatchesRegExpPattern',
		'pattern' => 'MatchesRegExpPattern',
		'endswith' => 'StringEndsWith',
		'ends' => 'StringEndsWith',
		'$' => 'StringEndsWith',
		'startswith' => 'StringStartsWith',
		'starts' => 'StringStartsWith',
		'^' => 'StringStartsWith',
		'stringcontains' => 'StringContains',
		'contains' => 'StringContains',

		'haskey' => 'ArrayHasKey',
		'hasitem' => 'TraversableContains',
		'hasonly' => 'TraversableContainsOnly',

		'unique' => 'IsUniqueInStorage',

		'fileexists' => 'FileExists',
		'file' => 'FileExists',

		'email' => 'IsEmail',
	);

	/** @var Rules[] */
	private $rules;



	/**
	 * @return Kdyby\Validation\Rules
	 */
	public function createRules()
	{
		return $this->rules[] = new Rules($this);
	}



	/**
	 * Helper for assigning event
	 *
	 * $rules->password(
	 *	~'empty',
	 *	$validator->on('flush:create')
	 * );
	 *
	 * @param string $event
	 * @param string $rule
	 * @return Helpers\Event
	 */
	public function on($event)
	{
		return new Helpers\Event($event);
	}



	/**
	 * @internal
	 * @param string $name
	 * @param array $args
	 * @return IConstraint
	 */
	public function createConstraint($name, $args)
	{
		$not = (is_string($name) && ord($name[0]) > 127);
		$class = $not ? ~$name : $name;
		array_unshift($args, $class);

		if (isset(self::$constraintsMap[$lowerName = strtolower($class)])) { // alias check
			$class = self::$constraintsMap[$lowerName];
		}

		$class = self::CONSTRAINT_NS . '\\' . $class . (!strpos($class, '::') ? '::create' : NULL);

		if (!is_callable($class)) {
			throw Kdyby\Tools\ExceptionFactory::invalidArgument(1, 'constraint or alias', func_get_arg(0));
		}

		$constraint = callback($class)->invokeArgs($args);
		return $not ? new Constraints\LogicalNot($constraint) : $constraint;
	}



	/**
	 * @param object $entity
	 * @param EntityManager $entityManager
	 */
	public function validateEntity($entity, EntityManager $entityManager)
	{
		return $this->validate(new PropertyDecorators\Entity($entity, $entityManager));
	}



	/**
	 * @param Nette\Forms\Container $form
	 */
	public function validateForm(Nette\Forms\Container $form)
	{
		return $this->validate(new PropertyDecorators\FormContainer($form));
	}



	/**
	 * @param IPropertyDecorator $decorator
	 * @param string|NULL $event
	 * @return Result
	 */
	public function validate(IPropertyDecorator $decorator, $event = NULL)
	{
		$result = new Result;

		foreach ($this->rules as $rules) {
			$result->import($rules->validate($decorator, $event));
		}

		return $result;
	}

}

