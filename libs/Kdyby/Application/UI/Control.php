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
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \Nette\Templating\FileTemplate $template
 * @method \Nette\Templating\FileTemplate getTemplate() getTemplate()
 *
 * @property-read \Kdyby\Application\UI\Presenter $presenter
 * @method \Kdyby\Application\UI\Presenter getPresenter() getPresenter(bool $need = TRUE)
 */
abstract class Control extends Nette\Application\UI\Control
{

	/** @var \Kdyby\Templates\ITemplateConfigurator */
	protected $templateConfigurator;



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
	 * @return \Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		if ($file = $this->getTemplateDefaultFile()) {
			$template->setFile($file);
		}

		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		return $template;
	}



	/**
	 * Derives template path from class name.
	 *
	 * @return null|string
	 */
	protected function getTemplateDefaultFile()
	{
		$refl = $this->getReflection();
		$file = dirname($refl->getFileName()) . '/' . $refl->getShortName() . '.latte';
		return file_exists($file) ? $file : NULL;
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
	 * @param string $name
	 *
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
		if ($element instanceof \Reflector && $presenter = $this->getPresenter(FALSE)) {
			$presenter->getUser()->protectElement($element);
		}
	}

}
