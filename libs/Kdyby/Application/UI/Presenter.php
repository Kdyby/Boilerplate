<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Nette;
use Nette\Diagnostics\Debugger;
use Kdyby;
use Kdyby\Application\Presentation\Bundle;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\DI\Container $context
 * @property Kdyby\Templates\ITheme $theme
 */
class Presenter extends Nette\Application\UI\Presenter
{

	/** @persistent */
	public $language = 'cs';

	/** @persistent */
	public $backlink;

	/** @var Kdyby\Templates\ITheme */
	private $theme;



	public function __construct()
	{
		parent::__construct(NULL, NULL);
		$this->theme = new Kdyby\Templates\Theme();
	}



	/**
	 * @return Kdyby\Templating\FileTemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = $this->getContext()->templateFactory->createTemplate($this, $class);
		$this->theme->setupTemplate($template);
		return $template;
	}



	/**
	 * @return Kdyby\Templates\ITheme
	 */
	public function getTheme()
	{
		return $this->theme;
	}



	/**
	 * @param Kdyby\Templates\ITheme $theme
	 */
	public function setTheme(Kdyby\Templates\ITheme $theme)
	{
		$this->theme = $theme;
	}



	/**
	 * @return string
	 */
	public function getModuleName()
	{
		return substr($this->getName(), 0, strpos($this->getName(), ':'));
	}



	/**
	 * If Debugger is enabled, print template variables to debug bar
	 */
	protected function afterRender()
	{
		parent::afterRender();

		if (Debugger::isEnabled()) { // todo: as panel
			Debugger::barDump($this->template->getParams(), 'Template variables');
		}
	}

}