<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Templates;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateFactoryTest extends Kdyby\Tests\TestCase
{
	/** @var \Kdyby\Templates\TemplateFactory */
	private $templateFactory;

	/** @var \Kdyby\Application\UI\Control */
	private $component;

	/** @var \Kdyby\DI\Container */
	private $context;



	public function setUp()
	{
		$this->templateFactory = new Kdyby\Templates\TemplateFactory(
			$this->getContext()->get('latte.engine'),
			$this->getContext()->get('http.context'),
			$this->getContext()->get('http.user'),
			$this->getContext()->get('cache.phpfile_storage'),
			$this->getContext()->get('cache.data_storage')
		);

		$this->component = new ControlMock();
	}



	public function testReturnsITemplate()
	{
		$instance = $this->templateFactory->createTemplate($this->component);
		$this->assertInstanceOf('Nette\Templating\ITemplate', $instance);
	}



	public function testReturnsTemplate()
	{
		$instance = $this->templateFactory->createTemplate($this->component);
		$this->assertInstanceOf('Nette\Templating\FileTemplate', $instance);

		$instance = $this->templateFactory->createTemplate($this->component, 'Nette\Templating\Template');
		$this->assertInstanceOf('Nette\Templating\Template', $instance);
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
		// presenter
		$this->component = $this->getMock('Kdyby\Application\UI\Presenter', array('hasFlashSession'));
		$this->component->setContext($this->getContext());

		$this->component->expects($this->once())
			->method('hasFlashSession')
			->will($this->returnValue(FALSE));

		// create template
		$this->templateFactory->createTemplate($this->component);
	}

}



namespace Kdyby\Tests\Templates;
class ControlMock extends \Kdyby\Application\UI\Control
{

}
