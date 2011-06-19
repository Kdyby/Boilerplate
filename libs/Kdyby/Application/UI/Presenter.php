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



	protected function beforeRender()
	{
		parent::beforeRender();
		$this->theme->installMacros($this->context->latteEngine->parser);
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



	/**
	 * Formats layout template file names.
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$name = trim($this->getName(), ':');
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$layout = $this->layout ? $this->layout : 'layout';

		$mapper = function ($dir) use ($presenter, $layout, $name) {
			$list = array(
				"$dir/templates/$presenter/@$layout.latte",
				"$dir/templates/$presenter.@$layout.latte",
			);
			do {
				$list[] = "$dir/templates/@$layout.latte";
				$dir = dirname($dir);
			} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
			return $list;
		};

		$list = array();
		$directories = $this->getContext()->moduleRegistry->getDirectories();
		foreach ($directories as $directory) {
			$dir = str_replace(':', '/', substr($name, 0, strrpos($name, ':')));
			$list = array_merge($list, $mapper($directory . '/' . $dir));
		}

		return $list;
	}



	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$name = trim($this->getName(), ':');
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$view = $this->view;

		$mapper = function ($dir) use ($presenter, $view, $name) {
			return array(
				"$dir/templates/$presenter/$view.latte",
				"$dir/templates/$presenter.$view.latte",
			);
		};

		$list = array();
		$directories = $this->getContext()->moduleRegistry->getDirectories();
		foreach ($directories as $directory) {
			$dir = str_replace(':', '/', substr($name, 0, strrpos($name, ':')));
			$list = array_merge($list, $mapper($directory . '/' . $dir));
		}

		return $list;
	}

}