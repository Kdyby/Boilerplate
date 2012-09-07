<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Assets;

use Assetic;
use Kdyby;
use Kdyby\Extension\Assets\FilterManager;
use Nette;
use Nette\DI\Container;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FilterManagerTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Extension\Assets\FilterManager */
	private $manager;

	/** @var \Nette\DI\Container */
	private $container;



	public function setUp()
	{
		$this->container = new Container();
		$this->manager = new FilterManager($this->container);
	}



	public function testProvidesRegisteredService()
	{
		$this->assertFalse($this->manager->has('foo'));

		$foo = new FilterMock();
		$this->container->addService('filter_foo', $foo);
		$this->manager->registerFilterService('filter_foo', 'foo');
		$this->assertTrue($this->manager->has('foo'));
		$this->assertSame($foo, $this->manager->get('foo'));
		$this->assertEquals(array('foo'), $this->manager->getNames());

		$bar = new FilterMock();
		$this->manager->set('bar', $bar);
		$this->assertSame($bar, $this->manager->get('bar'));
		$this->assertEquals(array('foo', 'bar'), $this->manager->getNames());
	}

}




