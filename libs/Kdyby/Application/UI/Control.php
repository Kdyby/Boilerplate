<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Kdyby\Templates\TemplateConfigurator;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \Nette\Templating\FileTemplate|\stdClass $template
 * @method \Nette\Templating\FileTemplate|\stdClass getTemplate() getTemplate()
 *
 * @property-read \Kdyby\Application\UI\Presenter $presenter
 * @method \Kdyby\Application\UI\Presenter getPresenter() getPresenter(bool $need = TRUE)
 */
abstract class Control extends Nette\Application\UI\Control
{

	/** @var \Kdyby\Templates\TemplateConfigurator */
	protected $templateConfigurator;



	/**
	 * @param \Kdyby\Templates\TemplateConfigurator $configurator
	 */
	public function setTemplateConfigurator(TemplateConfigurator $configurator = NULL)
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
	 * @return string|NULL
	 */
	protected function getTemplateDefaultFile()
	{
		$class = $this->getReflection();
		do {
			$file = dirname($class->getFileName()) . '/' . $class->getShortName() . '.latte';
			if (file_exists($file)) {
				return $file;

			} elseif (!$class = $class->getParentClass()) {
				break;
			}

		} while (TRUE);
	}



	/**
	 * Renders the default template
	 */
	public function render()
	{
		$this->template->render();
	}



	/**
	 * @param \Nette\Templating\Template $template
	 *
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		$engine = $this->getPresenter()->getContext()->nette->createLatte();
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->prepareFilters($engine);
		}

		$template->registerFilter($engine);
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
