<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tests\Constraint;

use Kdyby;
use Kdyby\Tests\Constraint\EventHasCallbackConstraint;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventHasCallbackConstraintTest extends Kdyby\Tests\TestCase
{

	/** @var Kdyby\Tests\Tests\Constraint\ObjectWithEventMock */
	private $object;

	/** @var string */
	private $class;



	public function setup()
	{
		$this->class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';

		$this->object = new ObjectWithEventMock();
		$this->object->onEvent[] = array($this->object, 'foo');
		$this->object->onEvent[] = array($this->class, 'staticFoo');
		$this->object->onEvent[] = callback($this->object, 'foo');
		$this->object->onEvent[] = callback($this->class, 'staticFoo');
	}



	public function testMatchingObjectInArrayCallback()
	{
		$constraint = new EventHasCallbackConstraint($this->object, 'onEvent', 2);
		$constraint->evaluate(array($this->object, 'foo'));
	}



	public function testMatchingClassInArrayCallback()
	{
		$constraint = new EventHasCallbackConstraint($this->object, 'onEvent', 2);
		$constraint->evaluate(array($this->class, 'staticFoo'));
	}



	public function testMatchingObjectInCallback()
	{
		$constraint = new EventHasCallbackConstraint($this->object, 'onEvent', 2);
		$constraint->evaluate(callback($this->object, 'foo'));
	}



	public function testMatchingClassInCallback()
	{
		$constraint = new EventHasCallbackConstraint($this->object, 'onEvent', 2);
		$constraint->evaluate(callback($this->class, 'staticFoo'));
	}

}
