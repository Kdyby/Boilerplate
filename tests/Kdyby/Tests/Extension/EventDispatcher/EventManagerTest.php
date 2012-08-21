<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\EventDispatcher;

use Kdyby;
use Kdyby\Extension\EventDispatcher\EventManager;
use Nette;



require_once __DIR__ . '/EventListenerMock.php';

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventManagerTest extends Kdyby\Tests\TestCase
{

	/** @var EventManager */
	private $manager;

	/** @var EventListenerMock|\PHPUnit_Framework_MockObject_MockObject */
	private $listener;



	public function setUp()
	{
		$this->manager = new EventManager;
		$this->listener = $this->getMockBuilder(__NAMESPACE__ . '\EventListenerMock')
			->setMethods(array('onFoo', 'onBar'))
			->getMock();
	}



	public function testListenerHasRequiredMethod()
	{
		$this->manager->addEventListener('onFoo', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertSame(array($this->listener), $this->manager->getListeners());
	}



	public function testRemovingListenerFromSpecificEvent()
	{
		$this->manager->addEventListener('onFoo', $this->listener);
		$this->manager->addEventListener('onBar', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$this->manager->removeEventListener('onFoo', $this->listener);
		$this->assertFalse($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));
	}



	public function testRemovingListenerCompletely()
	{
		$this->manager->addEventListener('onFoo', $this->listener);
		$this->manager->addEventListener('onBar', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$this->manager->removeEventListener($this->listener);
		$this->assertFalse($this->manager->hasListeners('onFoo'));
		$this->assertFalse($this->manager->hasListeners('onBar'));
		$this->assertSame(array(), $this->manager->getListeners());
	}



	/**
	 * @expectedException Kdyby\InvalidStateException
	 */
	public function testListenerDontHaveRequiredMethodException()
	{
		$this->manager->addEventListener('onNonexisting', $this->listener);
	}



	public function testDispatching()
	{
		$this->manager->addEventSubscriber($this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$eventArgs = new EventArgsMock();

		$this->listener->expects($this->once())
			->method('onFoo')
			->with($this->equalTo($eventArgs));

		$this->listener->expects($this->never())
			->method('onBar');

		$this->manager->dispatchEvent('onFoo', $eventArgs);
	}

}
