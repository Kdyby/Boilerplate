<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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

	/**
	 * @return array
	 */
	public function dataContains()
	{
		$object = new ObjectWithEventMock();
		$class = get_class($object);

		$object->onEvent[] = array($object, 'foo');
		$object->onEvent[] = array($class, 'staticFoo');
		$object->onEvent[] = callback($object, 'foo');
		$object->onEvent[] = callback($class, 'staticFoo');

		return array(
			array($object, 'onEvent', array($object, 'foo')),
			array($object, 'onEvent', callback($object, 'foo')),
			array($object, 'onEvent', array($class, 'staticFoo')),
			array($object, 'onEvent', callback($class, 'staticFoo')),
		);
	}



	/**
	 * @dataProvider dataContains
	 *
	 * @param \Nette\Object $object
	 * @param string $eventName
	 * @param callable $expected
	 */
	public function testContains(Nette\Object $object, $eventName, $expected)
	{
		$constraint = new EventHasCallbackConstraint($object, $eventName);
		$this->assertTrue($constraint->evaluate($expected, "Object has event listener", TRUE));
	}



	public function testGivenObjectDoesntSupportEventsException()
	{
		try {
			$object = new \stdClass;
			$constraint = new EventHasCallbackConstraint($object, 'onEvent');
			$constraint->evaluate('strtolower');

			$this->fail("Expected exception");

		} catch (\Exception $e) {
			$this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $e);
			list($message) = explode("\n", $e->getMessage(), 2);
			$this->assertSame("Given object does not supports events", $message);
		}
	}



	public function testGivenObjectDoesNotHaveAnEventException()
	{
		try {
			$object = new ObjectWithEventMock();
			$constraint = new EventHasCallbackConstraint($object, 'onNonExistingEvent');
			$constraint->evaluate('strtolower');

			$this->fail("Expected exception");

		} catch (\Exception $e) {
			$this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $e);
			list($message) = explode("\n", $e->getMessage(), 2);
			$this->assertSame("Object does not have an event onNonExistingEvent", $message);
		}
	}



	public function testEventDoesNotContainAnyListeners()
	{
		try {
			$object = new ObjectWithEventMock();
			$constraint = new EventHasCallbackConstraint($object, 'onEvent');
			$constraint->evaluate('strtolower');

			$this->fail("Expected exception");

		} catch (\Exception $e) {
			$this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $e);
			list($message) = explode("\n", $e->getMessage(), 2);
			$this->assertSame("Event does not contain any listeners", $message);
		}
	}



	public function testEventDoesNotContainGivenListeners()
	{
		try {
			$object = new ObjectWithEventMock();
			$object->onEvent[] = 'strtoupper';
			$constraint = new EventHasCallbackConstraint($object, 'onEvent');
			$constraint->evaluate('strtolower');

			$this->fail("Expected exception");

		} catch (\Exception $e) {
			$this->assertInstanceOf('PHPUnit_Framework_ExpectationFailedException', $e);
			list($message) = explode("\n", $e->getMessage(), 2);
			$this->assertSame("Event does not contain given listener", $message);
		}
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ObjectWithEventMock extends Nette\Object
{

	/** @var array */
	public $onEvent = array();

	public function foo() { }
	public static function staticFoo() { }
	public function __invoke() { return TRUE; }

}
