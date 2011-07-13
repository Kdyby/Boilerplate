<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing;

use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 */
class Test extends \PHPUnit_Framework_TestCase
{

	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 * @param Nette\Object $object
	 * @param string $eventName
	 */
	public function assertEventHasCallback($callback, $object, $eventName)
	{
		$this->assertInstanceOf('Nette\Object', $object, 'Object supports events');
		$this->assertObjectHasAttribute($eventName, $object, 'Object has event');

		$event = $object->$eventName;
		$this->assertNotEmpty($event, 'Event contains listeners');

		if (is_array($callback)) {
			$this->assertContainsOnly('array', $event, TRUE, 'Event contains only arrays');

		} elseif ($callback instanceof Nette\Callback) {
			$this->assertContainsOnly('Nette\Callback', $event, FALSE, 'Event contains only instances of Nette\Callback');
		}

		$targetIndex = array_search($callback, $event);
		$this->assertNotNull($targetIndex, 'Similar listener is in event');
		$target = $event[$targetIndex];

		if (is_array($callback)) {
			$this->assertNotNull($target[0]);
			$this->assertSame($callback[0], $target[0], 'Target matches');

			if (isset($callback[1])) {
				$this->assertNotNull($target[1]);
				$this->assertSame($callback[1], $target[1], 'Target matches');
			}
		}

		if ($callback instanceof Nette\Callback) {
			$this->assertNotNull($target->native[0]);
			$this->assertSame($callback->native[0], $target->native[0], 'Target matches');

			if (isset($callback->native[1])) {
				$this->assertNotNull($target->native[1]);
				$this->assertSame($callback->native[1], $target->native[1], 'Target matches');
			}
		}
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}