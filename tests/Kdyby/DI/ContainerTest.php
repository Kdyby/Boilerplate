<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\DI;

use Doctrine;
use Kdyby;
use Kdyby\DI\Container;
use Nette;



/**
 * @author Filip Procházka
 */
class ContainerTest extends Kdyby\Testing\TestCase
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



	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testServiceNameCannotContainDotException()
	{
		$this->container->addService("container.service", (object)NULL);
	}



	public function testGetServiceFromNestedContainer()
	{
		$service = (object)NULL;
		$nestedContainer = new Container();
		$nestedContainer->addService("service", $service);
		$this->container->addService("container", $nestedContainer);

		$this->assertSame($service, $this->container->getService('container.service'));
	}



	/**
	 * @expectedException Nette\DI\MissingServiceException
	 */
	public function testGetServiceNotInstanceofIContainerException()
	{
		$service = (object)NULL;
		$this->container->addService("container", $service);
		$this->container->getService("container.service");
	}



	public function testHasService()
	{
		$factoryCalled = FALSE;

		$nestedContainer = new Container();
		$nestedContainer->addService("service", function () use ($service, &$factoryCalled) {
			$factoryCalled = TRUE;
		});
		$this->container->addService("container", $nestedContainer);

		$this->assertTrue($this->container->hasService('container.service'));
		$this->assertFalse($factoryCalled);
	}



	/**
	 * @expectedException Nette\DI\MissingServiceException
	 */
	public function testHasServiceNotInstanceofIContainerException()
	{
		$service = (object)NULL;
		$this->container->addService("container", $service);
		$this->container->hasService("container.service");
	}

}