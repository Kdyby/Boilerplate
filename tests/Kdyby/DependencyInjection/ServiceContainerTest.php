<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace KdybyTests\DependencyInjection;

use Kdyby;
use Nette;



require_once __DIR__ . "/../../bootstrap.php";

class ServiceContainerTest extends Kdyby\Testing\TestCase
{
	/** @var Kdyby\DependencyInjection\ServiceContainer */
	private $serviceContainer;



	public function setUp()
	{
		$this->serviceContainer = new Kdyby\DependencyInjection\ServiceContainer;
	}



	public function testEnvironment()
	{
		$this->assertNull($this->serviceContainer->getEnvironment(), "default environment name not set");
		$this->serviceContainer->setEnvironment('foo');
		$this->assertEquals('foo', $this->serviceContainer->getEnvironment(), "->environment is 'foo'");
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testEnvironmentException()
	{
		$this->serviceContainer->freeze();
		$this->serviceContainer->setEnvironment('foo');
	}



	public function testParameters()
	{
		$this->serviceContainer->setParameter('foo', "Bar");
		$this->assertTrue($this->serviceContainer->hasParameter('foo'), "->hasParamter('foo') true after parameter set");
		$this->assertEquals("Bar", $this->serviceContainer->getParameter('foo'), "->getParameter('foo') equals 'bar'");
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testParamterNameException()
	{
		$this->serviceContainer->setParameter(NULL, "Foo");
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testParamterFrozenException()
	{
		$this->serviceContainer->freeze();
		$this->serviceContainer->setParameter('foo', "Bar");
	}



	public function testParametersExpandVar()
	{
		$this->serviceContainer->setParameter('foo', "test");
		$this->serviceContainer->setParameter('bar', "%foo%");
		$this->assertEquals("test", $this->serviceContainer->getParameter('foo'), "->getParameter('foo') equals 'test'");
		$this->assertEquals("test", $this->serviceContainer->getParameter('bar'), "->getParameter('bar') equals 'test' from foo paramter");
	}



	public function testParametersExpandService()
	{
		$this->serviceContainer->addService('Foo', new Foo);
		$this->serviceContainer->setParameter('foo', "@Foo");
		$this->assertTrue($this->serviceContainer->hasParameter('foo'), "->hasParamter('foo') true after parameter set");
		$this->assertEquals(new Foo, $this->serviceContainer->getParameter('foo'), "->getParameter('foo') equals Foo instance");
	}



	public function testBasicInstance()
	{
		$this->serviceContainer->addService('Test', new Foo);

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testBasicClass()
	{
		$this->serviceContainer->addService('Test', 'KdybyTests\DependencyInjection\Foo');

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testBasicFactory()
	{
		$this->serviceContainer->addService('Test', function() { return new Foo; });

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testBasicConstructorInjection()
	{
		$this->serviceContainer->addService('Test', 'KdybyTests\DependencyInjection\Foo', TRUE, array('arguments' => array("Test")));

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertEquals('Test', $this->serviceContainer->getService('Test')->bar, "service->bar equals 'Test'");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testBasicFactoryInjection()
	{
		$this->serviceContainer->addService('Test', function($bar) { return new Foo($bar); }, TRUE, array('arguments' => array("Test")));

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertEquals('Test', $this->serviceContainer->getService('Test')->bar, "service->bar equals 'Test'");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testBasicMethodInjectionClass()
	{
		$this->serviceContainer->addService(
			'Test',
			'KdybyTests\DependencyInjection\Foo',
			TRUE,
			array('methods' => array(
					array('method' => "setBar", 'arguments' => array("Test")),
				)
			)
		);

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertEquals('Test', $this->serviceContainer->getService('Test')->bar, "service->bar equals 'Test'");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testBasicMethodInjectionFactory()
	{
		$this->serviceContainer->addService(
			'Test',
			function() {
				return new Foo;
			},
			TRUE,
			array('methods' => array(
					array('method' => "setBar", 'arguments' => array("Test")),
				)
			)
		);

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertEquals('Test', $this->serviceContainer->getService('Test')->bar, "service->bar equals 'Test'");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testServiceConstructorInjection()
	{
		$this->serviceContainer->addService('Foo', new Foo);
		$this->serviceContainer->addService('Test', 'KdybyTests\DependencyInjection\Foo', TRUE, array('arguments' => array("@Foo")));

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertSame($this->serviceContainer->getService('Foo'), $this->serviceContainer->getService('Test')->bar, "service->bar is same as Foo service");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testServiceFactoryInjection()
	{
		$this->serviceContainer->addService('Foo', new Foo);
		$this->serviceContainer->addService('Test', function($bar) { return new Foo($bar); }, TRUE, array('arguments' => array("@Foo")));

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertSame($this->serviceContainer->getService('Foo'), $this->serviceContainer->getService('Test')->bar, "service->bar is same as Foo service");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testServiceMethodInjectionClass()
	{
		$this->serviceContainer->addService('Foo', new Foo);
		$this->serviceContainer->addService(
			'Test',
			'KdybyTests\DependencyInjection\Foo',
			TRUE,
			array('methods' => array(
					array('method' => "setBar", 'arguments' => array("@Foo")),
				)
			)
		);

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertSame($this->serviceContainer->getService('Foo'), $this->serviceContainer->getService('Test')->bar, "service->bar is same as Foo service");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	public function testServiceMethodInjectionFactory()
	{
		$this->serviceContainer->addService('Foo', new Foo);
		$this->serviceContainer->addService(
			'Test',
			function() {
				return new Foo;
			},
			TRUE,
			array('methods' => array(
					array('method' => "setBar", 'arguments' => array("@Foo")),
				)
			)
		);

		$this->assertTrue($this->serviceContainer->hasService('Test'), "has service Test");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $this->serviceContainer->getService('Test'), "get service Test (Foo instance)");
		$this->assertSame($this->serviceContainer->getService('Foo'), $this->serviceContainer->getService('Test')->bar, "service->bar is same as Foo service");
		$this->serviceContainer->removeService('Test');
		$this->assertFalse($this->serviceContainer->hasService('Test'), "has not service Test");
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testAddServiceFrozenException()
	{
		$this->serviceContainer->freeze();
		$this->serviceContainer->addService('Test', new Foo);
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testAddAliasFrozenException()
	{
		$this->serviceContainer->freeze();
		$this->serviceContainer->addAlias('Test', 'Test');
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testRemoveServiceFrozenException()
	{
		$this->serviceContainer->addService('Test', new Foo);
		$this->serviceContainer->freeze();
		$this->serviceContainer->removeService('Test');
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddServiceBadNameException()
	{
		$this->serviceContainer->addService('', new Foo);
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddServiceBadServiceException()
	{
		$this->serviceContainer->addService('Test', NULL);
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddServiceSignletonInstanceException()
	{
		$this->serviceContainer->addService('Test', new Foo, FALSE);
	}



	/**
	 * @expectedException Nette\AmbiguousServiceException
	 */
	public function testAddServiceRegisteredException()
	{
		$this->serviceContainer->addService('Test', new Foo);
		$this->serviceContainer->addService('Test', new Foo);
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddAliasBadNameException()
	{
		$this->serviceContainer->addAlias('', 'Test');
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddAliasBadServiceException()
	{
		$this->serviceContainer->addAlias('Test', '');
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testAddAliasNotExistServiceException()
	{
		$this->serviceContainer->addAlias('Test', 'Test');
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testGetServiceNonExistException()
	{
		$this->serviceContainer->getService('Test');
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetServiceInstanceOptionsException()
	{
		$this->serviceContainer->addService('Test', new Foo);
		$this->serviceContainer->getService('Test', array("foo"));
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testRemoveServiceBadNameException()
	{
		$this->serviceContainer->removeService('');
	}



	public function testGetConstantParameter()
	{
		$this->assertEquals(APP_DIR, $this->serviceContainer->getParameter('appDir'), '->getParameter("appDir") equals APP_DIR');
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testGetNonExistParameter()
	{
		$this->serviceContainer->getParameter('iLoveNetteFrameworkAndNetteFrameworkCreator!Really');
	}



	public function testGetFactory()
	{
		$this->serviceContainer->addService('Test', 'KdybyTests\DependencyInjection\Foo');
		$factory = $this->serviceContainer->getFactory('Test');

		$this->assertInstanceOf('Kdyby\DependencyInjection\IServiceFactory', $factory, "->getFactory('Test') instance of IServiceFactory");
		$this->assertInstanceOf('KdybyTests\DependencyInjection\Foo', $factory->getInstance(), "\$factory->getInstance() instance of defined service");
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testGetNonExistFactoryException()
	{
		$this->serviceContainer->getFactory('Test');
	}



	public function testSetFactory()
	{
		$factory = new Kdyby\DependencyInjection\ServiceFactory($this->serviceContainer, 'Test');
		$this->serviceContainer->addFactory($factory);

		$this->assertSame($factory, $this->serviceContainer->getFactory('Test'));
	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testSetFrozenFactoryException()
	{
		$this->serviceContainer->freeze();

		$factory = new Kdyby\DependencyInjection\ServiceFactory($this->serviceContainer, 'Test');
		$this->serviceContainer->addFactory($factory);
	}



	/**
	 * @expectedException Nette\AmbiguousServiceException
	 */
	public function testSetExistingFactoryException()
	{
		$this->serviceContainer->addService('Test', new Foo);

		$factory = new Kdyby\DependencyInjection\ServiceFactory($this->serviceContainer, 'Test');
		$this->serviceContainer->addFactory($factory);
	}

}
