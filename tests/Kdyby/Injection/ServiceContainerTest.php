<?php

namespace KdybyTests\Injection;

use Kdyby;
use Nette;



# autoloading and stuff
require_once __DIR__ . '/../../bootstrap.php';

class ServiceContainerTest extends Kdyby\Testing\TestCase
{

	/** @var Kdyby\Injection\ServiceContainer */
	protected $container;

	/** @var Kdyby\Injection\ServiceBuilder */
	protected $builder;



	protected function setUp()
	{
		$this->container = new Kdyby\Injection\ServiceContainer();
		$this->builder = new Kdyby\Injection\ServiceBuilder($this->container);
	}



	public function testDefinedByClassWithoutParamsWithoutCalls()
	{
		$this->container->addService('Service', 'KdybyTests\Injection\Mocks\ServiceMock');

		$this->assertTrue($this->container->hasService('Service'));
		$this->assertInstanceOf('KdybyTests\Injection\Mocks\ServiceMock', $this->container->getService('Service'));

		$this->container->removeService('Service');
		$this->assertFalse($this->container->hasService('Service'));

		try {
			$service = $this->container->getService('Service');

		} catch (\Exception $e) {
			$this->assertInstanceOf('InvalidStateException', $e);
			return;
		}

		$this->fail('An expected exception has not been raised.');
	}



	public function testDefinedByFactoryWithParamsWithoutCalls()
	{
		$options = array(
			'entityDir' => array('%appDir%', '%libsDir%'),
			'proxyDir' => array('%proxiesDir%'),
		);

		$this->container->addService('Service', 'KdybyTests\Injection\Mocks\ServiceMock::createServiceMock', TRUE, $options);

		$this->assertTrue($this->container->hasService('Service'));
		$this->assertInstanceOf('KdybyTests\Injection\Mocks\ServiceMock', $this->container->getService('Service'));

		$this->assertSame($options['entityDir'], $this->container->getService('Service')->propertyConstructor[0]);
		$this->assertSame($options['proxyDir'], $this->container->getService('Service')->propertyConstructor[1]);

		$this->container->removeService('Service');
		$this->assertFalse($this->container->hasService('Service'));

		try {
			$service = $this->container->getService('Service');

		} catch (\Exception $e) {
			$this->assertInstanceOf('InvalidStateException', $e);
			return;
		}

		$this->fail('An expected exception has not been raised.');
	}

}