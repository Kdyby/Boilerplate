<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Config;

use Kdyby;
use Kdyby\Config\TaggedServices;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TaggedServicesTest extends Kdyby\Tests\TestCase
{

	/**
	 * @var \Nette\DI\Container
	 */
	private $container;



	public function setup()
	{
		$this->container = new Nette\DI\Container();

		$this->container->addService('one', function () {
			return (object)array('id' => 1);
		}, array(
			Nette\DI\Container::TAGS => array('lorem' => 'ipsum')
		));

		$this->container->addService('two', function () {
			return (object)array('id' => 2);
		}, array(
			Nette\DI\Container::TAGS => array('lorem' => 'ipsum')
		));

		$this->container->addService('three', function () {
			return (object)array('id' => 3);
		}, array(
			Nette\DI\Container::TAGS => array('lorem' => 'dolor')
		));

		$this->container->addService('four', function () {
			return (object)array('id' => 4);
		});
	}



	public function testFindTaggedServices()
	{
		$list = new TaggedServices('lorem', $this->container);
		$this->assertEquals(array(
			(object)array('id' => 1),
			(object)array('id' => 2),
			(object)array('id' => 3),
		), iterator_to_array($list));
	}



	public function testTrullyLazy()
	{
		$list = new TaggedServices('lorem', $this->container);

		$this->assertFalse($this->container->isCreated('one'));
		$this->assertFalse($this->container->isCreated('two'));
		$this->assertFalse($this->container->isCreated('three'));
		$this->assertFalse($this->container->isCreated('four'));

		foreach ($list as $name => $service) {
			if ($name === 0) { // one
				$this->assertTrue($this->container->isCreated('one'));
				$this->assertFalse($this->container->isCreated('two'));
				$this->assertFalse($this->container->isCreated('three'));
				$this->assertFalse($this->container->isCreated('four'));

			} elseif ($name === 1) { // two
				$this->assertTrue($this->container->isCreated('one'));
				$this->assertTrue($this->container->isCreated('two'));
				$this->assertFalse($this->container->isCreated('three'));
				$this->assertFalse($this->container->isCreated('four'));

			} elseif ($name === 2) { // three
				$this->assertTrue($this->container->isCreated('one'));
				$this->assertTrue($this->container->isCreated('two'));
				$this->assertTrue($this->container->isCreated('three'));
				$this->assertFalse($this->container->isCreated('four'));

			} else {
				$this->fail('Unexpected key');
			}
		}
	}



	public function testFindByMeta()
	{
		$list = new TaggedServices('lorem', $this->container);
		$this->assertEquals((object)array('id' => 3), $list->findOneByMeta('dolor'));
		$this->assertEquals((object)array('id' => 1), $list->findOneByMeta('ipsum'));

		$this->assertEquals(array(
			(object)array('id' => 3)
		), $list->findByMeta('dolor'));

		$this->assertEquals(array(
			(object)array('id' => 1),
			(object)array('id' => 2),
		), $list->findByMeta('ipsum'));
	}

}
