<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Application;

use Kdyby;
use Kdyby\Application\PresenterManager;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PresenterManagerTest extends Kdyby\Tests\TestCase
{

	/** @var  */
	private $manager;



	public function setup()
	{
		$pm = new \Kdyby\Package\PackageManager();
		$pm->activate($this->getPackages());

		$container = new \Kdyby\DI\Container();
		$container->setParameter('productionMode', TRUE);
		$this->manager = new PresenterManager($pm,
			$container,
			$this->getContext()->expand('%appDir%')
		);
	}



	/**
	 * @return array
	 */
	private function getPackages()
	{
		$defaultPackages = new \Kdyby\Packages\DefaultPackages();
		return array_merge($defaultPackages->getPackages(), array(
			'Kdyby\\Tests\\Application\\Mocks\\BarPackage\\BarPackage',
			'Kdyby\\Tests\\Application\\Mocks\\FooPackage\\FooPackage',
		));
	}



	/**
	 * @return array
	 */
	public function dataPackagePresentersFormats()
	{
		return array(
			array('Kdyby\FooPackage\Presenter\FooPresenter', 'FooPackage:Foo'),
			array('Kdyby\FooPackage\Presenter\BarPresenter', 'FooPackage:Bar'),
			array('Kdyby\BarPackage\Presenter\FooPresenter', 'BarPackage:Foo'),
			array('Kdyby\BarPackage\Presenter\BarPresenter', 'BarPackage:Bar'),
			array('Kdyby\BarPackage\Presenter\FooFooPresenter', 'BarPackage:FooFoo'),
			array('Kdyby\BarPackage\Presenter\FooModule\FooBarPresenter', 'BarPackage:Foo:FooBar'),
		);
	}



	/**
	 * @dataProvider dataPackagePresentersFormats
	 *
	 * @param string $class
	 * @param string $expected
	 */
	public function testFormatNameFromPackageClass($class, $expected)
	{
		$this->assertEquals($expected, $this->manager->formatPresenterFromClass($class));
	}



	/**
	 * @return array
	 */
	public function dataServiceNamesAndPresenters()
	{
		return array(
			array('FooPackage:Foo', 'foo_package.foo_presenter'),
			array('BarPackage:FooFoo', 'bar_package.foo_foo_presenter'),
			array('BarPackage:Foo:FooBar', 'bar_package.foo.foo_bar_presenter'),
			array('FooBarPackage:Foo:FooBar', 'foo_bar_package.foo.foo_bar_presenter'),
		);
	}



	/**
	 * @dataProvider dataServiceNamesAndPresenters
	 *
	 * @param string $presenterName
	 * @param string $serviceName
	 */
	public function testServiceNameFormating($presenterName, $serviceName)
	{
		$this->assertEquals($serviceName, $this->manager->formatServiceNameFromPresenter($presenterName), "Formating service name from presenter name");
		$this->assertEquals($presenterName, $this->manager->formatPresenterFromServiceName($serviceName), "Formating presenter name from service name");
	}



	/**
	 * @return array
	 */
	public function dataPackagePresenters()
	{
		return array(
			array('Kdyby\Tests\Application\Mocks\FooPackage\Presenter\FooPresenter', 'FooPackage:Foo'),
			array('Kdyby\Tests\Application\Mocks\FooPackage\Presenter\BarPresenter', 'FooPackage:Bar'),
			array('Kdyby\Tests\Application\Mocks\BarPackage\Presenter\FooPresenter', 'BarPackage:Foo'),
			array('Kdyby\Tests\Application\Mocks\BarPackage\Presenter\BarPresenter', 'BarPackage:Bar'),
			array('Kdyby\Tests\Application\Mocks\FooPackage\Presenter\BarModule\BarBarPresenter', 'FooPackage:Bar:BarBar'),
		);
	}



	/**
	 * @dataProvider dataPackagePresenters
	 *
	 * @param string $class
	 * @param string $name
	 */
	public function testCreatePresenterFromPackageUsingContainer($class, $name)
	{
		$pm = new \Kdyby\Package\PackageManager();
		$pm->activate($this->getPackages());

		$manager = new PresenterManager($pm,
			$this->createContainerWithPresenters(),
			$this->getContext()->expand('%appDir%')
		);

		$this->assertInstanceof($class, $manager->createPresenter($name));
	}



	/**
	 * @return \Kdyby\DI\Container
	 */
	private function createContainerWithPresenters()
	{
		$presenters = array(
			'Kdyby\Tests\Application\Mocks\FooPackage\Presenter\FooPresenter' => 'FooPackage:Foo',
			'Kdyby\Tests\Application\Mocks\FooPackage\Presenter\BarPresenter' => 'FooPackage:Bar',
			'Kdyby\Tests\Application\Mocks\BarPackage\Presenter\FooPresenter' => 'BarPackage:Foo',
			'Kdyby\Tests\Application\Mocks\BarPackage\Presenter\BarPresenter' => 'BaPackager:Bar',
			'Kdyby\Tests\Application\Mocks\FooPackage\Presenter\BarModule\BarBarPresenter' => 'FooPackage:Bar:BarBar',
		);

		$container = new \Kdyby\DI\Container();
		foreach ($presenters as $presenterClass => $presenter) {
			$serviceName = $this->manager->formatServiceNameFromPresenter($presenter);
			$container->set($serviceName, new $presenterClass);
		}

		$container->setParameter('productionMode', TRUE);
		return $container;
	}



	/**
	 * @dataProvider dataPackagePresenters
	 *
	 * @param string $class
	 * @param string $name
	 */
	public function testCreatePresenterFromPackageUsingClassGuessing($class, $name)
	{
		$this->assertInstanceof($class, $this->manager->createPresenter($name));
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 2
	 */
	public function testGetPresenterClassForInvalidNameException()
	{
		$name = ' ' . Nette\Utils\Strings::random();
		$this->manager->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 3
	 */
	public function testGetPresenterClassMissingException()
	{
		$name = 'BarPackage:MissingPresenter' . Nette\Utils\Strings::random();
		$this->manager->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 4
	 */
	public function testGetPresenterClassImplementsInterfaceException()
	{
		$name = 'FooPackage:Fake';
		$this->manager->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 5
	 */
	public function testGetPresenterClassAbstractException()
	{
		$name = 'FooPackage:Abstract';
		$this->manager->getPresenterClass($name);
	}



	/**
	 * @expectedException Kdyby\Application\InvalidPresenterException
	 * @expectedExceptionCode 6
	 */
	public function testGetPresenterClassCaseSensitiveException()
	{
		$name = 'FooPackage:homepage';

		$this->manager->caseSensitive = TRUE;
		$this->manager->getPresenterClass($name);
	}

}




/** Bar package simulation */
namespace Kdyby\Tests\Application\Mocks\BarPackage;
class BarPackage extends \Kdyby\Package\Package
{

}

namespace Kdyby\Tests\Application\Mocks\BarPackage\Presenter;
class FooPresenter extends \Kdyby\Application\UI\Presenter
{

}



class BarPresenter extends \Kdyby\Application\UI\Presenter
{

}

/** Foo package simulation */
namespace Kdyby\Tests\Application\Mocks\FooPackage;
class FooPackage extends \Kdyby\Package\Package
{

}

namespace Kdyby\Tests\Application\Mocks\FooPackage\Presenter;
class FooPresenter extends \Kdyby\Application\UI\Presenter
{

}



class BarPresenter extends \Kdyby\Application\UI\Presenter
{

}



class HomepagePresenter extends \Kdyby\Application\UI\Presenter
{

}



abstract class AbstractPresenter extends \Kdyby\Application\UI\Presenter
{

}



class FakePresenter
{

}

namespace Kdyby\Tests\Application\Mocks\FooPackage\Presenter\BarModule;
class BarBarPresenter extends \Kdyby\Application\UI\Presenter
{

}
