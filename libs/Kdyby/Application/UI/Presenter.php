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
use Kdyby\Templates\ITemplateFactory;
use Nette;
use Nette\Application\Responses;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \Kdyby\DI\Container $context
 * @property-read \Kdyby\Http\User $user
 *
 * @method \Kdyby\Http\User getUser() getUser()
 */
abstract class Presenter extends Nette\Application\UI\Presenter
{

	/** @var \Kdyby\Templates\TemplateFactory */
	protected $templateFactory;



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);
		$this->templateFactory = $container->templateFactory;
	}



	/**
	 * @param string|null $class
	 *
	 * @return \Kdyby\Templating\Template
	 */
	protected function createTemplate($class = NULL)
	{
		return $this->templateFactory->createTemplate($this, $class);
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
	 * If Debugger is enabled, print template variables to debug bar
	 */
	protected function afterRender()
	{
		parent::afterRender();
		Kdyby\Diagnostics\TemplateParametersPanel::register($this);
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

}
