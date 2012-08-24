<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\DI;

use Kdyby;
use Kdyby\Extension\DI\FactoryGeneratorExtension;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FactoryGeneratorExtensionTest extends Kdyby\Tests\TestCase
{

	public function testFunctionality()
	{
		$container = $this->createContainer(__DIR__ . '/files/factories.neon', array(
			'dicFactories' => new FactoryGeneratorExtension()
		));

		/** @var Foo $foo */
		$foo = $container->getService('foo');
		$this->assertInstanceOf(__NAMESPACE__ . '\\Foo', $foo);
		$this->assertInstanceOf(__NAMESPACE__ . '\\IBarFactory', $foo->factory);

		/** @var Bar $bar */
		$bar = $foo->factory->create();
		$this->assertInstanceOf(__NAMESPACE__ . '\\Bar', $bar);
		$this->assertInstanceOf('Nette\Application\Application', $bar->app);
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Foo extends Nette\Object
{

	/**
	 * @var IBarFactory
	 */
	public $factory;



	/**
	 * @param IBarFactory $factory
	 */
	public function __construct(IBarFactory $factory)
	{
		$this->factory = $factory;
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Bar extends Nette\Object
{

	/**
	 * @var \Nette\Application\Application
	 */
	public $app;



	/**
	 * @param \Nette\Application\Application $app
	 */
	public function __construct(Nette\Application\Application $app)
	{
		$this->app = $app;
	}

}
