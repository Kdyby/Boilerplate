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
use Kdyby\Tests\Constraint\CallbackEqualsCallbackConstraint;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CallbackEqualsCallbackConstraintTest extends Kdyby\Tests\TestCase
{

	public function testIsMethodOnObject()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		$constraint = new CallbackEqualsCallbackConstraint('is_bool');
		$this->assertTrue($constraint->isMethodOnObject(array($object, 'foo')));
		$this->assertTrue($constraint->isMethodOnObject(callback($object, 'foo')->getNative()));
		$this->assertFalse($constraint->isMethodOnObject(array($class, 'staticFoo')));
		$this->assertFalse($constraint->isMethodOnObject(callback($class, 'staticFoo')->getNative()));
		$this->assertFalse($constraint->isMethodOnObject($closure));
		$this->assertFalse($constraint->isMethodOnObject(callback($closure)->getNative()));
		$this->assertFalse($constraint->isMethodOnObject($object));
		$this->assertFalse($constraint->isMethodOnObject(callback($object)->getNative()));
		$this->assertFalse($constraint->isMethodOnObject('is_bool'));
		$this->assertFalse($constraint->isMethodOnObject(callback('is_bool')->getNative()));
	}



	public function testIsMethodOnClass()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		$constraint = new CallbackEqualsCallbackConstraint('is_bool');
		$this->assertFalse($constraint->isMethodOnClass(array($object, 'foo')));
		$this->assertFalse($constraint->isMethodOnClass(callback($object, 'foo')->getNative()));
		$this->assertTrue($constraint->isMethodOnClass(array($class, 'staticFoo')));
		$this->assertTrue($constraint->isMethodOnClass(callback($class, 'staticFoo')->getNative()));
		$this->assertFalse($constraint->isMethodOnClass($closure));
		$this->assertFalse($constraint->isMethodOnClass(callback($closure)->getNative()));
		$this->assertFalse($constraint->isMethodOnClass($object));
		$this->assertFalse($constraint->isMethodOnClass(callback($object)->getNative()));
		$this->assertFalse($constraint->isMethodOnClass('is_bool'));
		$this->assertFalse($constraint->isMethodOnClass(callback('is_bool')->getNative()));
	}



	public function testIsClosure()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		$constraint = new CallbackEqualsCallbackConstraint('is_bool');
		$this->assertFalse($constraint->isClosure(array($object, 'foo')));
		$this->assertFalse($constraint->isClosure(callback($object, 'foo')->getNative()));
		$this->assertFalse($constraint->isClosure(array($class, 'staticFoo')));
		$this->assertFalse($constraint->isClosure(callback($class, 'staticFoo')->getNative()));
		$this->assertTrue($constraint->isClosure($closure));
		$this->assertTrue($constraint->isClosure(callback($closure)->getNative()));
		$this->assertFalse($constraint->isClosure($object));
		$this->assertFalse($constraint->isClosure(callback($object)->getNative()));
		$this->assertFalse($constraint->isClosure('is_bool'));
		$this->assertFalse($constraint->isClosure(callback('is_bool')->getNative()));
	}



	public function testIsCallableObject()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		$constraint = new CallbackEqualsCallbackConstraint('is_bool');
		$this->assertFalse($constraint->isCallableObject(array($object, 'foo')));
		$this->assertFalse($constraint->isCallableObject(callback($object, 'foo')->getNative()));
		$this->assertFalse($constraint->isCallableObject(array($class, 'staticFoo')));
		$this->assertFalse($constraint->isCallableObject(callback($class, 'staticFoo')->getNative()));
		$this->assertFalse($constraint->isCallableObject($closure));
		$this->assertFalse($constraint->isCallableObject(callback($closure)->getNative()));
		$this->assertTrue($constraint->isCallableObject($object));
		$this->assertTrue($constraint->isCallableObject(callback($object)->getNative()));
		$this->assertFalse($constraint->isCallableObject('is_bool'));
		$this->assertFalse($constraint->isCallableObject(callback('is_bool')->getNative()));
	}



	public function testIsFunction()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		$constraint = new CallbackEqualsCallbackConstraint('is_bool');
		$this->assertFalse($constraint->isFunction(array($object, 'foo')));
		$this->assertFalse($constraint->isFunction(callback($object, 'foo')->getNative()));
		$this->assertFalse($constraint->isFunction(array($class, 'staticFoo')));
		$this->assertFalse($constraint->isFunction(callback($class, 'staticFoo')->getNative()));
		$this->assertFalse($constraint->isFunction($closure));
		$this->assertFalse($constraint->isFunction(callback($closure)->getNative()));
		$this->assertFalse($constraint->isFunction($object));
		$this->assertFalse($constraint->isFunction(callback($object)->getNative()));
		$this->assertTrue($constraint->isFunction('is_bool'));
		$this->assertTrue($constraint->isFunction(callback('is_bool')->getNative()));
	}



	public function dataEquals()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		return array(
			array( // object method
				array($object, 'foo'), array($object, 'foo')
			), array(
				callback($object, 'foo'), callback($object, 'foo')
			), array(
				callback($object, 'foo'), array($object, 'foo')
			), array(
				array($object, 'foo'), callback($object, 'foo')
			), array( // class
				array($class, 'staticFoo'), array($class, 'staticFoo')
			), array(
				callback($class, 'staticFoo'), callback($class, 'staticFoo')
			), array(
				callback($class, 'staticFoo'), array($class, 'staticFoo')
			), array(
				array($class, 'staticFoo'), callback($class, 'staticFoo')
			), array( // closure
				$closure, $closure
			), array(
				callback($closure), $closure
			), array(
				$closure, callback($closure)
			), array(
				callback($closure), callback($closure)
			), array( // __invoke
				$object, $object
			), array(
				callback($object), $object
			), array(
				$object, callback($object)
			), array(
				callback($object), callback($object)
			)
		);
	}



	/**
	 * @dataProvider dataEquals
	 *
	 * @param $me
	 * @param $other
	 */
	public function testEquals($me, $other)
	{
		$constraint = new CallbackEqualsCallbackConstraint($me);
		$constraint->evaluate($other);
	}



	public function dataNotEquals()
	{
		$class = 'Kdyby\Tests\Tests\Constraint\ObjectWithEventMock';
		$object = new ObjectWithEventMock();
		$closure = function () { };

		return array(
			array(
				array($object, 'foo'), array($class, 'foo')
			), array(
				array($object, 'foo'), array($class, 'staticFoo')
			), array(
				array($object, 'foo'), $closure
			), array(
				array($object, 'foo'), $object
			), array(
				array($class, 'foo'), $closure
			), array(
				array($class, 'foo'), $object
			), array(
				$closure, $object
			)
		);
	}



	/**
	 * @dataProvider dataNotEquals
	 *
	 * @param $me
	 * @param $other
	 */
	public function testNotEquals($me, $other)
	{
		try {
			$constraint = new CallbackEqualsCallbackConstraint($me);
			$constraint->evaluate($other);

		} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
			// pass
		}
	}

}



class ObjectWithEventMock extends Nette\Object
{

	/** @var array */
	public $onEvent = array();

	public function foo() { }
	public static function staticFoo() { }

}
