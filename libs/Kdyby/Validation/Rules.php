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
class Rules extends Nette\Object
{

	/** @var array */
	private $rules = array();

	/** @var Rules */
	private $parent;

	/** @var Validator */
	private $validator;



	/**
	 * @param Validator $validator
	 */
	public function __construct(Validator $validator, Rules $parent = NULL)
	{
		$this->validator = $validator;
		$this->parent = $parent;
	}



	/**
	 * @property IPropertyDecorator $decorator
	 * @property string|NULL $event
	 * @return Result
	 */
	public function validate(IPropertyDecorator $decorator, $event = NULL)
	{
		if ($event !== NULL && !is_string($event)) {
			throw Kdyby\Tools\ExceptionFactory::invalidArgument(1, 'string');
		}

		$result = new Result;

		foreach ($this->rules as $rule) {
			if (isset($rule->on) && $event !== NULL && !in_array($event, $rule->on)) {
				continue;
			}

			$success = $this->doEvaluateRule($rule, $decorator);

			if ($success instanceof Error) {
				if ($rule instanceof Rules\Validator) {
					$result->addError($success);
				}

				continue;
			}

			if ($rule instanceof Rules\Condition) {
				$result->import($rule->rules->validate($decorator));

			} elseif ($rule instanceof Rules\Relation) {
				foreach ($rule->getRelated($decorator) as $related) {
					$result->import($rule->rules->validate($decorator->decorate($related)));
				}

			} elseif ($rule instanceof Rules\Collection) {
				$collection = $decorator->decorate($rule->getCollection($decorator));
				$result->import($rule->rules->validate($collection));
			}
		}

		return $result;
	}



	/**
	 * @param string $propertyName
	 * @param string $constraint
	 * @param mixed $arg
	 * @param mixed $event
	 * @return Kdyby\Validation\Rules
	 */
	public function addRule($propertyName, $constraint, $arg = NULL, $event = NULL)
	{
		$args = func_get_args();
		$propertyName = array_shift($args);
		$constraint = array_shift($args);
		$events = $this->doPopEvents($args);

		$this->rules[] = new Rules\Validator(
				$this->validator->createConstraint($constraint, array($propertyName) + $args),
				$propertyName
			);

		end($this->rules)->on($events);

		return $this;
	}



	/**
	 * @param string $propertyName
	 * @param string $constraint
	 * @param mixed $arg
	 * @param mixed $event
	 * @return Kdyby\Validation\Rules
	 */
	public function addCondition($propertyName, $constraint, $arg = NULL, $event = NULL)
	{
		$args = func_get_args();
		$propertyName = array_shift($args);
		$constraint = array_shift($args);
		$events = $this->doPopEvents($args);

		$this->rules[] = new Rules\Condition(
				$this->validator->createConstraint($constraint, array($propertyName) + $args),
				$rules = new static($this->validator, $this),
				$propertyName
			);

		end($this->rules)->on($events);

		return $rules;
	}



	/**
	 * @param array $args
	 * @return array
	 */
	protected function doPopEvents(array &$args)
	{
		$events = array();
		while (is_object(end($args)) && end($args) instanceof Helpers\Event) {
			$event = array_pop($args);
			$events[] = $event->name;
		}
		return $events;
	}



	/**
	 * @param string $propertyName
	 * @return Kdyby\Validation\Rules
	 */
	public function getRelation($propertyName)
	{
		if (!is_string($propertyName) || $propertyName == "") {
			throw Kdyby\Tools\ExceptionFactory::invalidArgument(2, 'non-empty string', $propertyName);
		}

		$this->rules[] = new Rules\Relation($propertyName, $rules = new static($this->validator, $this));
		return $rules;
	}



	/**
	 * @param string $propertyName
	 * @return Kdyby\Validation\Rules
	 */
	public function getCollection($propertyName)
	{
		if (!is_string($propertyName) || $propertyName == "") {
			throw Kdyby\Tools\ExceptionFactory::invalidArgument(2, 'non-empty string', $propertyName);
		}


		$this->rules[] = new Rules\Collection($propertyName, $rules = new static($this->validator, $this));
		return $rules;
	}



	/**
	 * Adds a else statement.
	 * @return Rules else branch
	 */
	public function elseCondition()
	{
		$rule = clone end($this->parent->rules);
		$rule->isNegative = !$rule->isNegative;
		$rule->subRules = new static($this->parent->control);
		$rule->subRules->parent = $this->parent;
		$this->parent->rules[] = $rule;
		return $rule->subRules;
	}



	/**
	 * Ends current validation condition.
	 * @return Rules parent branch
	 */
	public function endCondition()
	{
		return $this->parent;
	}



	/**
	 * Iterates over ruleset.
	 * @return \ArrayIterator
	 */
	final public function getIterator()
	{
		return new \ArrayIterator($this->rules);
	}



	/**
	 * @param object $rule
	 * @param IPropertyDecorator $decorator
	 * @return NULL|TRUE|Error
	 */
	private function doEvaluateRule($rule, IPropertyDecorator $decorator)
	{
		if (!isset($rule->constraint) || !isset($rule->property)) {
			return NULL;
		}

		try {
			$other = $rule->property
				? $decorator->getValue($rule->property)
				: $decorator->getEntity();

			$success = $rule->constraint->evaluate($other);
			if (!$success) {
				$rule->constraint->fail(
					$other,
					isset($rule->message)? $rule->message : NULL,
					$decorator->getEntity(),
					$rule->property
				);
			}

		} catch (Error $error) {
			return $error;
		}

		return TRUE;
	}

}