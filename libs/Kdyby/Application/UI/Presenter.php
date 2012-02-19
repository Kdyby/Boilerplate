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
 * @property-read \Kdyby\Security\User $user
 * @property-read \Nette\Templating\Template $template
 * @property-read \Kdyby\Doctrine\Registry $doctrine
 *
 * @method \Kdyby\Security\User getUser() getUser()
 * @method \SystemContainer|\Nette\DI\Container getContext() getContext()
 */
abstract class Presenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $backlink;

	/** @var \Kdyby\Templates\TemplateConfigurator */
	protected $templateConfigurator;



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Nette\DI\Container $container)
	{
		parent::__construct($container);

		if ($container->hasService('templateConfigurator')) {
			$this->setTemplateConfigurator($container->templateConfigurator);
		}
	}



	/**
	 * @return \Kdyby\Doctrine\Registry
	 */
	public function getDoctrine()
	{
		return $this->getContext()->doctrine->registry;
	}



	/**
	 * @param string $entity
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function getRepository($entity)
	{
		return $this->getDoctrine()->getRepository($entity);
	}



	/**
	 * @param \Kdyby\Templates\ITemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(ITemplateConfigurator $configurator = NULL)
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
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		if (!$this->isInPackage()) {
			return parent::formatLayoutTemplateFiles();
		}

		$presenter = substr($name = $this->getName(), strrpos(':' . $name, ':'));
		$layout = $this->layout ? $this->layout : 'layout';

		$mapper = function ($views) use ($presenter, $layout) {
			return array(
				"$views/$presenter/@$layout.latte",
				"$views/$presenter.@$layout.latte",
				"$views/@$layout.latte",
			);
		};

		return array_merge(
			$mapper(realpath(dirname($this->getReflection()->getFileName()) . '/../Resources/view')),
			$mapper($this->context->expand('%appDir%/templates'))
		);
	}



	/**
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		if (!$this->isInPackage()) {
			return parent::formatTemplateFiles();
		}

		$presenter = substr($name = $this->getName(), strrpos(':' . $name, ':'));
		$view = $this->view;

		$mapper = function ($views) use ($presenter, $view) {
			return array(
				"$views/$presenter/$view.latte",
				"$views/$presenter.$view.latte",
			);
		};

		return array_merge(
			$mapper(realpath(dirname($this->getReflection()->getFileName()) . '/../Resources/view')),
			$mapper($this->context->expand('%appDir%/templates'))
		);
	}



	/**
	 * Presenter is in package, when "Package" keyword is in it's namespace
	 * and "Module" keyword is not. Because packages disallow modules.
	 *
	 * @return bool
	 */
	private function isInPackage()
	{
		return stripos(get_called_class(), 'Package\\') !== FALSE
			&& stripos(get_called_class(), 'Module\\') === FALSE;
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
