<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Templates;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class TemplateFactoryTest extends Kdyby\Testing\TestCase
{
	/** @var Nette\Latte\Engine */
	private $latteEngine;

	/** @var Kdyby\Templates\TemplateFactory */
	private $templateFactory;

	/** @var Kdyby\Application\UI\Control */
	private $component;

	/** @var Kdyby\DI\Container */
	private $context;



	public function setUp()
	{
		$this->latteEngine = new Nette\Latte\Engine;
		$this->templateFactory = new Kdyby\Templates\TemplateFactory($this->latteEngine);

		$this->component = new ControlMock();
	}



	public function testReturnsITemplate()
	{
		$instance = $this->templateFactory->createTemplate($this->component);
		$this->assertInstanceOf('Nette\Templating\ITemplate', $instance);
	}



	public function testReturnsKdybyTemplate()
	{
		$instance = $this->templateFactory->createTemplate($this->component);
		$this->assertInstanceOf('Kdyby\Templates\FileTemplate', $instance);
	}



	public function testIntegrationCreateTemplateForControl()
	{
		$this->component = $this->getMock('Kdyby\Application\UI\Control');

		$this->component->expects($this->once())
			->method('getPresenter')
			->with($this->equalTo(FALSE))
			->will($this->returnValue(NULL));

		$this->templateFactory->createTemplate($this->component);
	}



	public function testIntegrationCreateTemplateForPresenter()
	{
		// context
		$this->context = $this->getMock('Kdyby\DI\Container');
		$this->context->expects($this->at(0))
			->method('getService')
			->with($this->equalTo('templateCacheStorage'))
			->will($this->returnValue($this->getMock('Nette\Caching\Storages\PhpFileStorage', array(), array(), "", FALSE)));

		$this->context->expects($this->at(1))
			->method('getService')
			->with($this->equalTo('httpResponse'))
			->will($this->returnValue($this->getMock('Nette\Http\IResponse')));

		$this->context->expects($this->at(2))
			->method('getService')
			->with($this->equalTo('cacheStorage'))
			->will($this->returnValue($this->getMock('Nette\Caching\Storages\FileStorage', array(), array(), "", FALSE)));

		$this->context->expects($this->at(3))
			->method('getParam')
			->with($this->equalTo('baseUrl'))
			->will($this->returnValue('http://example.com/folder'));

		$this->context->expects($this->at(4))
			->method('getParam')
			->with($this->equalTo('basePath'))
			->will($this->returnValue('/folder'));


		// presenter
		$this->component = $this->getMock('Kdyby\Application\UI\Presenter', array(
			'getPresenter',
			'getUser',
			'getHttpResponse',
			'hasFlashSession'
		));
		$this->component->setContext($this->context);
		$this->component->expects($this->never())
			->method('getPresenter');

		$this->component->expects($this->once())
			->method('getUser')
			->will($this->returnValue($this->getMock('Nette\Http\User', array(), array($this->context))));

		$this->component->expects($this->never())
			->method('getHttpResponse');

		$this->component->expects($this->once())
			->method('hasFlashSession')
			->will($this->returnValue(FALSE));

		// create template
		$this->templateFactory->createTemplate($this->component);
	}

}