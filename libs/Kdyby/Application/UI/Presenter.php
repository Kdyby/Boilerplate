<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Kdyby\Templates\ITemplateConfigurator;
use Nette;
use Nette\Application\Responses;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \SystemContainer|\Nette\DI\Container $container
 * @property-read \Kdyby\Http\User $user
 *
 * @method \Kdyby\Http\User getUser() getUser()
 */
abstract class Presenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $backlink;

	/** @var \SystemContainer|\Nette\DI\Container */
	private $container;

	/** @var \Kdyby\Templates\ITemplateConfigurator */
	protected $templateConfigurator;



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);
		$this->container = $container;

		if ($container->hasService('templateConfigurator')) {
			$this->setTemplateConfigurator($container->templateConfigurator);
		}
	}



	/**
	 * @todo temporary solution!
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}



	/**
	 * @param \Kdyby\Templates\ITemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(ITemplateConfigurator $configurator)
	{
		$this->templateConfigurator = $configurator;
	}



	/**
	 * @param string|null $class
	 *
	 * @return \Nette\Templating\Template
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		return $template;
	}



	/**
	 * @param \Nette\Templating\Template $template
	 *
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->prepareFilters($template);

		} else {
			$template->registerFilter(new Nette\Latte\Engine);
		}
	}



	/**
	 * Sends AJAX payload to the output.
	 *
	 * @param array|object|null $payload
	 *
	 * @return void
	 * @throws \Nette\Application\AbortException
	 */
	public function sendPayload($payload = NULL)
	{
		if ($payload !== NULL) {
			$this->sendResponse(new Responses\JsonResponse($payload));
		}

		parent::sendPayload();
	}



	/**
	 * @param string $name
	 * @return \Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		$method = 'createComponent' . ucfirst($name);
		if (method_exists($this, $method)) {
			$this->checkRequirements($this->getReflection()->getMethod($method));
		}

		return parent::createComponent($name);
	}



	/**
	 * Checks for requirements such as authorization.
	 *
	 * @param \Reflector $element
	 *
	 * @return void
	 */
	public function checkRequirements($element)
	{
		if ($element instanceof \Reflector) {
			$this->getUser()->protectElement($element);
		}
	}



	/**
	 * If Debugger is enabled, print template variables to debug bar
	 */
	protected function afterRender()
	{
		parent::afterRender();
		Kdyby\Diagnostics\TemplateParametersPanel::register($this);
	}

}
