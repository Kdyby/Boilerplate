<?php

namespace NetteTests\Application;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class PresenterFactoryTest extends Kdyby\Testing\TestCase
{

	/** @var Nette\Application\PresenterFactory */
	private $factory;

	/** @var Nette\DI\Container */
	private $context;

	/** @var string */
	private $appDir = APP_DIR;



	public function setUp()
	{
		$this->context = new Nette\DI\Container;
		$this->factory = new Nette\Application\PresenterFactory($this->appDir, $this->context);
	}



	public function testInterface()
	{
		$this->assertInstanceOf('Nette\Application\IPresenterFactory', $this->factory);
	}



	public function presenterClassNames()
	{
		return array(
			array('Homepage', 'HomepagePresenter'),
			array('Front:Homepage', 'FrontModule\HomepagePresenter'),
			array('Front:Forum:Homepage', 'FrontModule\ForumModule\HomepagePresenter'),
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
			array('Homepage', $this->appDir . '/presenters/HomepagePresenter.php'),
			array('Front:Homepage', $this->appDir . '/FrontModule/presenters/HomepagePresenter.php'),
			array('Front:Forum:Homepage', $this->appDir . '/FrontModule/ForumModule/presenters/HomepagePresenter.php'),
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



	public function validAndExistingPresenters()
	{
		return array(
			array('Homepage', 'HomepagePresenter'),
			array('Front:Homepage', 'FrontModule\HomepagePresenter'),
			array('Front:Forum:Homepage', 'FrontModule\ForumModule\HomepagePresenter'),
		);
	}



	/**
	 * @dataProvider validAndExistingPresenters
	 */
	public function testGetPresenterClass($presenter, $class)
	{
		$ret = $this->factory->getPresenterClass($presenter);
		$this->assertEquals($class, $ret);
	}



	/**
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function testGetPresenterClassForInvalidNameException()
	{
		$name = ' ' . Nette\Utils\Strings::random();
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function testGetPresenterClassMissingException()
	{
		$name = 'MissingPresenter' . Nette\Utils\Strings::random();
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function testGetPresenterClassImplementsInterfaceException()
	{
		$name = 'Fake';
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function testGetPresenterClassAbstractException()
	{
		$name = 'Abstract';
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @expectedException Nette\Application\InvalidPresenterException
	 */
	public function testGetPresenterClassCaseSensitiveException()
	{
		$name = 'homepage';

		$this->factory->caseSensitive = TRUE;
		$this->factory->getPresenterClass($name);
	}



	/**
	 * @dataProvider validAndExistingPresenters
	 */
	public function testCreatePresenter($presenter, $class)
	{
		$instance = $this->factory->createPresenter($presenter);
		$this->assertInstanceOf($class, $instance);
	}



	/**
	 * @dataProvider validAndExistingPresenters
	 */
	public function testCreatedPresenterHasContext($presenter, $class)
	{
		$instance = $this->factory->createPresenter($presenter);
		$this->assertSame($this->context, $instance->getContext());
	}

}