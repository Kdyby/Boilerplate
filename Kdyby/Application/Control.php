<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Environment;
use Nette\Utils\Strings;



/**
 * @property-read Presenter $presenter
 * @property Kdyby\Templates\FileTemplate $template
 *
 * @method Kdyby\Templates\FileTemplate getTemplate() getTemplate()
 * @method Kdyby\Application\Presenter getPresenter() getPresenter()
 */
class Control extends Nette\Application\UI\Control
{

	/**
	 * @return Kdyby\Security\User
	 */
	public function getUser()
	{
		return $this->getPresenter()->getService('Nette\Http\IUser');
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->getPresenter()->getService('Doctrine\ORM\EntityManager');
	}



	/**
	 * @return Nette\Localization\ITranslator
	 */
	public function getTranslator()
	{
		return $this->getPresenter()->getService("Nette\\Localization\\ITranslator");
	}



	/**************************** Templates ****************************/



	/**
	 * @param string|NULL $class
	 * @return Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$templateFactory = $this->getPresenter()->getService('Kdyby\Templates\ITemplateFactory');
		return $templateFactory->createTemplate($this, $class);
	}



	/**************************** Components magic ****************************/



	/**
	 * @param string $name
	 * @return Nette\ComponentModel\Component
	 */
	public function createComponent($name)
	{
		$component = parent::createComponent($name);
		return Kdyby\Components\Helpers::createComponent($this, $component, $name);
	}

}