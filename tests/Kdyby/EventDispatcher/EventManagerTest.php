<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\EventDispatcher;

use Kdyby;
use Kdyby\EventDispatcher\EventManager;
use Nette;



/**
 * @author Filip Procházka
 */
class EventManagerTest extends Kdyby\Testing\Test
{

	/** @var EventManager */
	private $manager;

	/** @var EventListenerMock|\PHPUnit_Framework_MockObject_MockObject */
	private $listener;



	public function setUp()
	{
		$this->manager = new EventManager;
		$this->listener = $this->getMock('Kdyby\Testing\EventDispatcher\EventListenerMock', array('onFoo', 'onBar'));
	}



	public function testListenerHasRequiredMethod()
	{
		$this->manager->addListener('onFoo', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertSame(array($this->listener), $this->manager->getListeners());
	}



	public function testRemovingListenerFromSpecificEvent()
	{
		$this->manager->addListener('onFoo', $this->listener);
		$this->manager->addListener('onBar', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$this->manager->removeListener($this->listener, 'onFoo');
		$this->assertFalse($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));
	}



	public function testRemovingListenerCompletely()
	{
		$this->manager->addListener('onFoo', $this->listener);
		$this->manager->addListener('onBar', $this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$this->manager->removeListener($this->listener);
		$this->assertFalse($this->manager->hasListeners('onFoo'));
		$this->assertFalse($this->manager->hasListeners('onBar'));
		$this->assertSame(array(), $this->manager->getListeners());
	}



	/**
	 * @expectedException Nette\InvalidStateException
	 */
	public function testListenerDontHaveRequiredMethodException()
	{
		$this->manager->addListener('onNonexisting', $this->listener);
	}



	public function testDispatching()
	{
		$this->manager->addSubscriber($this->listener);
		$this->assertTrue($this->manager->hasListeners('onFoo'));
		$this->assertTrue($this->manager->hasListeners('onBar'));

		$eventArgs = new EventArgsMock();

		$this->listener->expects($this->once())
			->method('onFoo')
			->with($this->equalTo($eventArgs));

		$this->listener->expects($this->never())
			->method('onBar');

		$this->manager->dispatch('onFoo', $eventArgs);
	}

}