<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Application;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class PresenterFactoryTest extends \PHPUnit_Framework_TestCase
{

	/** @var Kdyby\Application\PresenterFactory */
	private $factory;

	/** @var Nette\DI\Container */
	private $context;



	public function setUp()
	{
		$this->context = new Nette\DI\Container;
		$this->context->addService('moduleRegister', function () {
			return new Kdyby\Tools\FreezableArray(array(
				'Kdyby\Module' => KDYBY_DIR . '/Modules',
				'App' => APP_DIR,
				'Foo' => APP_DIR,
			));
		});

		$this->context->moduleRegister->freeze();
		$this->factory = new Kdyby\Application\PresenterFactory($this->context->moduleRegister, $this->context);
	}



	public function testInterface()
	{
		$this->assertInstanceOf('Nette\Application\IPresenterFactory', $this->factory);
	}



	public function presenterClassNames()
	{
		return array(
			array('Front:Homepage', 'Front\HomepagePresenter'),
			array('Front:Forum:Homepage', 'Front\Forum\HomepagePresenter'),
		);
	}



	/**
	 * @dataProvider presenterClassNames
	 */
	public function testFormatPresenterClass($presenter, $class)
	{
		$ret = $this->factory->formatPresenterClass($presenter);
		$this->assertEquals($class, $ret);
	}



	/**
	 * @dataProvider presenterClassNames
	 */
	public function testUnformatPresenterClass($presenter, $class)
	{
		$ret = $this->factory->unformatPresenterClass($class);
		$this->assertEquals($presenter, $ret);
	}



	public function presenterPathNames()
	{
		return array(
			array('Front:Homepage', APP_DIR . '/Front/presenters/HomepagePresenter.php'),
			array('Front:Forum:Homepage', APP_DIR . '/Front/Forum/presenters/HomepagePresenter.php'),
			array('Back:Homepage', APP_DIR . '/Back/presenters/HomepagePresenter.php'),
			array('Back:Forum:Homepage', APP_DIR . '/Back/Forum/presenters/HomepagePresenter.php'),
		);
	}



	/**
	 * @dataProvider presenterPathNames
	 */
	public function testFormatPresenterFile($presenter, $path)
	{
		$ret = $this->factory->formatPresenterFile($presenter);
		$this->assertEquals($path, $ret);
	}



	public function presenterClassNamesPrefixed()
	{
		return array(
			array('Front:Homepage', 'App\Front\HomepagePresenter'),
			array('Front:Forum:Homepage', 'App\Front\Forum\HomepagePresenter'),
			array('Back:Homepage', 'Foo\Back\HomepagePresenter'),
			array('Back:Forum:Homepage', 'Foo\Back\Forum\HomepagePresenter'),
			array('Admin:Dashboard', 'Kdyby\Module\Admin\DashboardPresenter'),
			array('Admin:List', 'Kdyby\Module\Admin\ListPresenter'),
			array('Admin:Articles:List', 'Kdyby\Module\Admin\Articles\ListPresenter'),
		);
	}



	/**
	 * @dataProvider presenterClassNamesPrefixed
	 */
	public function testGetPresenterClass($presenter, $class)
	{
		$ret = $this->factory->getPresenterClass($presenter);
		$this->assertEquals($class, $ret);
	}



	/**
	 * @dataProvider presenterClassNamesPrefixed
	 */
	public function testGetPresenterClassFixCaseByReference($name, $class)
	{
		$originalName = $name;
		$ret = $this->factory->getPresenterClass($name);
		$this->assertSame($originalName, $name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 2
	 */
	public function testGetPresenterClassForInvalidNameException()
	{
		$name = ' ' . Nette\Utils\Strings::random();
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 3
	 */
	public function testGetPresenterClassMissingException()
	{
		$name = 'Front:MissingPresenter' . Nette\Utils\Strings::random();
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 4
	 */
	public function testGetPresenterClassImplementsInterfaceException()
	{
		$name = 'Front:Fake';
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 5
	 */
	public function testGetPresenterClassAbstractException()
	{
		$name = 'Front:Abstract';
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 6
	 */
	public function testGetPresenterClassCaseSensitiveException()
	{
		$name = 'Front:homepage';

		$this->factory->caseSensitive = TRUE;
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @dataProvider presenterClassNamesPrefixed
	 */
	public function testCreatePresenter($presenter, $class)
	{
		$instance = $this->factory->createPresenter($presenter);
		$this->assertInstanceOf($class, $instance);
	}



	/**
	 * @dataProvider presenterClassNamesPrefixed
	 */
	public function testCreatedPresenterHasContext($presenter, $class)
	{
		$instance = $this->factory->createPresenter($presenter);
		$this->assertSame($this->context, $instance->getContext());
	}

}