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
use Nette\Config\Config;
use Nette\Environment;



require_once __DIR__ . "/../../bootstrap.php";

class ServiceContainerBuilderTest extends Kdyby\Testing\TestCase
{
	/** @var ServiceContainerBuilderMock */
	private $builder;



	public function setUp()
	{
		$this->builder = new ServiceContainerBuilderMock;
		Environment::setConfigurator($this->builder);
	}



	public function testSetServiceContainerClass()
	{
		$class = 'Kdyby\DependencyInjection\ServiceContainer';
		$this->builder->setServiceContainerClass($class);
		$this->assertInstanceOf($class, $this->builder->getServiceContainer());
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetServiceContainerClassException1()
	{
		$this->builder->setServiceContainerClass(NULL);
	}



	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testSetServiceContainerClassException2()
	{
		$this->builder->setServiceContainerClass(get_called_class());
	}



	public function testDetectEnvironment()
	{
		$this->builder->loadEnvironmentNameMock("foo");
		$this->assertEquals("foo", Environment::getVariable('environment'),
			"Environment::getVariable environment name equals 'foo"
		);
		$this->assertEquals("foo", $this->builder->serviceContainer->getEnvironment(), '$serviceContainer->environment equals "foo"');
	}



	public function testLoadIni()
	{
		$data = array(
			'include_path' => get_include_path(),
			'iconv.internal_encoding' => iconv_get_encoding(),
			'mbstring.internal_encoding' => mb_internal_encoding(),
			'date' => array('timezone' => "Europe/Prague"),
			'error_reporting' => E_ALL | E_STRICT,
			'ignore_user_abort' => ignore_user_abort(),
			'max_execution_time' => 0,
		);
		$this->builder->loadIniMock(new Config($data));
		$this->assertEquals($data['date']['timezone'], date_default_timezone_get());

	}



	/**
	 * @expectedException InvalidStateException
	 */
	public function testLoadIniException()
	{
		$data = array(
			'xxx' => (object) array('xxx'),
		);
		$this->builder->loadIniMock(new Config($data));

	}



	public function testLoadParametersVariables()
	{
		$data = array('variables' => array('foo' => "Bar"));
		$this->builder->loadParametersMock(new Config($data));
		$this->assertTrue($this->builder->serviceContainer->hasParameter('foo'), "exist variable foo");
		$this->assertEquals("Bar", $this->builder->serviceContainer->getParameter('foo'), "variable foo equals 'Bar'");
		$this->assertEquals("Bar", Environment::getVariable('foo'), "variable foo equals 'Bar'");
	}



	public function testLoadParametersNormal()
	{
		$data = array(
			'foo' => "Bar",
			'bar' => array('baz' => array('test' => "Test"), 'xxx' => "xXx"),
		);

		$this->builder->loadParametersMock(new Config($data));
		$this->assertTrue($this->builder->serviceContainer->hasParameter('foo'), "exist config foo");
		$this->assertEquals("Bar", $this->builder->serviceContainer->getParameter('foo'), "config foo equals 'Bar'");

		$this->assertTrue($this->builder->serviceContainer->hasParameter('bar'), "exist config bar");
		$this->assertEquals($data['bar'], $this->builder->serviceContainer->getParameter('bar'), "config bar equals array");
		$data = $this->builder->serviceContainer->getParameter('bar');
		$this->assertInternalType('array', $data['baz'], "config bar equals array");

		$this->assertFalse($this->builder->serviceContainer->hasParameter('baz'), "not exist confg or variable baz");
	}

}
