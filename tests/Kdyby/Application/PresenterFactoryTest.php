<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace KdybyTests\Application;

use Kdyby;
use Kdyby\DependencyInjection\ServiceContainerBuilder;
use Nette;



require_once __DIR__ . "/../../bootstrap.php";

class PresenterFactoryTest extends Kdyby\Testing\TestCase
{
	/** @var Nette\Application\IPresenterFactory */
	private $factory;



	public function setUp()
	{
		$serviceContainer = new Kdyby\DependencyInjection\ServiceContainer();
		$serviceContainer->addService('Kdyby\Registry\NamespacePrefixes', ServiceContainerBuilder::createRegistryNamespacePrefixes());
		$serviceContainer->addService('Kdyby\Registry\TemplateDirs', ServiceContainerBuilder::createRegistryTemplateDirs());

		$this->factory = new Kdyby\Application\PresenterFactory($serviceContainer->getService('Kdyby\Registry\NamespacePrefixes'));
		$this->factory->setServiceContainer($serviceContainer);
	}



	/**
	 * @test
	 */
	public function formatPresenterClass()
	{
		$this->assertEquals('App\FooPresenter', $this->factory->formatPresenterClass('Foo'), "->formatPresenterClass('Foo')");
		$this->assertEquals('App\Foo\BarPresenter', $this->factory->formatPresenterClass('Foo:Bar'), "->formatPresenterClass('Foo:Bar')");
		$this->assertEquals('App\Foo\Bar\BazPresenter', $this->factory->formatPresenterClass('Foo:Bar:Baz'), "->formatPresenterClass('Foo:Bar:Baz')");
		$this->assertEquals('Kdyby\FooPresenter', $this->factory->formatPresenterClass('Foo', 'framework'), "->formatPresenterClass('Foo', 'lib')");
		$this->assertEquals('Kdyby\Foo\BarPresenter', $this->factory->formatPresenterClass('Foo:Bar', 'framework'), "->formatPresenterClass('Foo:Bar', 'lib')");
		$this->assertEquals('Kdyby\Foo\Bar\BazPresenter', $this->factory->formatPresenterClass('Foo:Bar:Baz', 'framework'), "->formatPresenterClass('Foo:Bar:Baz', 'lib')");
	}



	/**
	 * @test
	 */
	public function unformatPresenterClass()
	{
		$this->assertEquals('Foo', $this->factory->unformatPresenterClass('App\FooPresenter'), "->unformatPresenterClass('App\\FooPresenter')");
		$this->assertEquals('Foo:Bar', $this->factory->unformatPresenterClass('App\Foo\BarPresenter'), "->unformatPresenterClass('App\\Foo\\BarPresenter')");
		$this->assertEquals('Foo:Bar:Baz', $this->factory->unformatPresenterClass('App\Foo\Bar\BazPresenter'), "->unformatPresenterClass('App\\Foo\\Bar\\BazPresenter')");
		$this->assertEquals('Foo', $this->factory->unformatPresenterClass('Kdyby\FooPresenter'), "->unformatPresenterClass('Nella\\FooPresenter')");
		$this->assertEquals('Foo:Bar', $this->factory->unformatPresenterClass('Kdyby\Foo\BarPresenter'), "->unformatPresenterClass('Nella\\Foo\\BarPresenter')");
		$this->assertEquals('Foo:Bar:Baz', $this->factory->unformatPresenterClass('Kdyby\Foo\Bar\BazPresenter'), "->unformatPresenterClass('Nella\\Foo\\Bar\\BazPresenter')");
	}



	/**
	 * @test
	 */
	public function getPresenterClass()
	{
		$name = 'Foo';
		$this->assertEquals('App\FooPresenter', $this->factory->getPresenterClass($name), "->getPresenterClass('$name')");

		$name = 'Bar:Foo';
		$this->assertEquals('App\Bar\FooPresenter', $this->factory->getPresenterClass($name), "->getPresenterClass('$name')");

		$name = 'My';
		$this->assertEquals('Kdyby\MyPresenter', $this->factory->getPresenterClass($name), "->getPresenterClass('$name')");

		$name = 'Foo:My';
		$this->assertEquals('Kdyby\Foo\MyPresenter', $this->factory->getPresenterClass($name), "->getPresenterClass('$name')");
	}



	/**
	 * @test
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function getPresenterClassEmptyNameException()
	{
		$name = NULL;
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @test
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function getPresenterClassInterfaceException()
	{
		$name = 'Baz';
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @test
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function getPresenterClassAbstractException()
	{
		$name = 'Bar';
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @test
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function getPresenterClassCaseSensitiveException()
	{
		$this->factory->caseSensitive = TRUE;

		$name = 'my';
		$this->factory->getPresenterClass($name);
	}
}


namespace Kdyby;

class MyPresenter extends \Kdyby\Application\Presenter { }

namespace Kdyby\Foo;

class MyPresenter extends \Kdyby\Application\Presenter { }

namespace App;

class FooPresenter extends \Kdyby\Application\Presenter { }
abstract class BarPresenter extends \Kdyby\Application\Presenter { }
class BazPresenter { }

namespace App\Bar;

class FooPresenter extends \Kdyby\Application\Presenter { }