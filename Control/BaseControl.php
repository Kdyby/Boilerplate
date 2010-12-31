<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Control;

use Kdyby;
use Nette;
use Nette\Environment;
use Nette\String;



class BaseControl extends Nette\Application\Control
{

	/** @var Nette\ITranslator */
	private $translator;



	/**
	 * @return Nette\Web\IUser
	 */
	public function getUser()
	{
		return Nette\Environment::getUser();
	}



	/**
	 * @return Nette\ITranslator
	 */
	public function getTranslator()
	{
		if ($this->translator === NULL) {
			$this->translator = Environment::getService("Nette\\ITranslator");
		}

		return $this->translator;
	}


	/**
	 * @param Nette\ITranslator $translator
	 */
	public function setTranslator(Nette\ITranslator $translator)
	{
		$this->translator = $translator;
	}



	/*=========================== Templates =============================*/



	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->setTranslator($this->getTranslator());

		// TODO: themes, think harder dude!
		$action = ltrim($this->presenter->getAction(TRUE), ':');
		$module = String::lower(substr($action, 0, strpos($action, ':')));
		$theme = Environment::getConfig("theme")->{$module};

		$template->theme = $template->basePath . '/theme_' . $theme;
		$template->user = $this->getUser();

		return $template;
	}


	public function templatePrepareFilters($template)
	{
		$this->presenter->templatePrepareFilters($template);
	}



	/*=========================== Components magic =============================*/



	public function createComponent($name)
	{
		$component = parent::createComponent($name);
		return Kdyby\Component\Helpers::createComponent($this, $component, $name);
	}

}
