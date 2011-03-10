<?php

namespace KdybyTests\Injection;

use Kdyby;
use Nette;



# autoloading and stuff
require_once __DIR__ . '/../../bootstrap.php';

class ServiceBuilderTest extends Kdyby\Testing\TestCase
{

	/** @var Kdyby\Injection\ServiceContainer */
	protected $container;

	/** @var Kdyby\Injection\ServiceBuilder */
	protected $builder;



	protected function setUp()
	{
		$this->container = new Kdyby\Injection\ServiceContainer();
		$this->builder = new Kdyby\Injection\ServiceBuilder($this->container);

		$this->container->addService('Test\DummyService1', (object)array('gender' => 'woman'));
		$this->container->addService('Test\DummyService2', (object)array('gender' => 'man'));
	}



	public function testCreationOfDescription()
	{
		$name = 'Test\Service';
		$service = 'KdybyTests\Injection\Mocks\ServiceMock::createServiceMock';
		$options = array(
			'entityDir' => array(
				'%appDir%',
				'%libsDir%',
			),
			'proxyDir' => array(
				'%tempDir%/proxies',
			),
		);

		$description = $this->builder->createDescription($service, $options);

		$this->assertSame($options, $description->arguments);
	}



	public function testServiceArguments()
	{
		$this->assertContains($this->container->getService('Test\DummyService2'), $this->builder->processArguments(array('§Test\DummyService2')));
	}



	public function invalidDescriptionProvider()
	{
		$description = new Kdyby\Injection\Description('KdybyTests\Injection\Mocks\ServiceMock');

		// add arguments
		$description->setArguments(array('%Test\DummyService1', '%Test\DummyService2'));
		// $description->autowire = TRUE; // default value

		return array(array($description));
	}



	/**
	 * @dataProvider invalidDescriptionProvider
	 * @expectedException InvalidArgumentException
	 */
	public function testServiceFactoryAutowireFail(Kdyby\Injection\Description $description)
	{
		$service = $this->builder->serviceFactory(array('description' => $description));
	}



	public function validDescriptionProvider()
	{
		$description = new Kdyby\Injection\Description('KdybyTests\Injection\Mocks\ServiceMock');

		// add arguments
		$description->setArguments(array('§Test\DummyService1', '§Test\DummyService2'));
		$description->autowire = FALSE;

		// add method call
		$description->addMethodCall('dance', array('funky'));
		$description->addMethodCall('shine');

		// add properties
		$description->addProperty('gender', 'male');
		$description->addProperty('penis', TRUE);

		return array(array($description));
	}



	/**
	 * @dataProvider validDescriptionProvider
	 */
	public function testServiceFactory(Kdyby\Injection\Description $description)
	{
		// create service
		$service = $this->builder->serviceFactory(array('description' => $description));

		// test setup
		$this->assertContains($this->container->getService('Test\DummyService1'), $service->propertyConstructor);
		$this->assertContains($this->container->getService('Test\DummyService2'), $service->propertyConstructor);

		$this->assertContains(array('dance', array('funky')), $service->calledMethod);
		$this->assertContains(array('shine', array()), $service->calledMethod);

		$this->assertContains(array('gender', 'male'), $service->propertySet);
		$this->assertContains(array('penis', TRUE), $service->propertySet);
	}
	

}