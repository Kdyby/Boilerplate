<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tests;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TestTest extends Kdyby\Tests\TestCase
{

	public function testMatchingCallbacks()
	{
		$class = 'Kdyby\Tests\Tests\ObjectWithEventMock';

		$object = new $class();
		$object->onEvent[] = array($object, 'foo');
		$object->onEvent[] = array($class, 'staticFoo');
		$object->onEvent[] = callback($object, 'foo');
		$object->onEvent[] = callback($class, 'staticFoo');

		$this->assertEventHasCallback(array($object, 'foo'), $object, 'onEvent', 2);
		$this->assertEventHasCallback(array($class, 'staticFoo'), $object, 'onEvent', 2);
		$this->assertEventHasCallback(callback($object, 'foo'), $object, 'onEvent', 2);
		$this->assertEventHasCallback(callback($class, 'staticFoo'), $object, 'onEvent', 2);
	}

}