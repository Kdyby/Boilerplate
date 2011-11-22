<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\DI;

use Doctrine;
use Kdyby;
use Kdyby\DI\Container;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ContainerTest extends Kdyby\Tests\TestCase
{

	/** @var Container */
	private $container;



	public function setup()
	{
		$this->container = new Container();
	}



	public function testGetParam()
	{
		$this->container->params['key'] = "value";
		$this->assertSame("value", $this->container->getParam('key'));
	}



	public function testGetParamDefaultValue()
	{
		$this->assertSame("default", $this->container->getParam("key", "default"));
	}



	public function testLazyCopy()
	{
		$factoryCalled = FALSE;

		$sourceContainer = new Nette\DI\Container();
		$sourceContainer->addService("service", function () use (&$factoryCalled) {
			$factoryCalled = TRUE;
			return (object)NULL;
		});

		$this->assertFalse($factoryCalled);
		$this->container->lazyCopy("service", $sourceContainer);
		$this->assertFalse($factoryCalled);

		$service = $this->container->getService("service");
		$this->assertTrue($factoryCalled);
		$this->assertInstanceOf('stdClass', $service);
	}

}