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
use Nette\String;



/**
 * @property-read Kdyby\Application\Presenter $presenter
 * @property Kdyby\Templates\FileTemplate $template
 *
 * @method Kdyby\Templates\FileTemplate getTemplate() getTemplate()
 * @method Kdyby\Application\Presenter getPresenter() getPresenter()
 */
class Control extends Nette\Application\Control
{

	/**
	 * @return Kdyby\Security\User
	 */
	public function getUser()
	{
		return $this->getPresenter()->getService('Nette\Web\IUser');
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->getPresenter()->getService('Doctrine\ORM\EntityManager');
	}



	/**
	 * @return Nette\ITranslator
	 */
	public function getTranslator()
	{
		return $this->getPresenter()->getService("Nette\\ITranslator");
	}



	/**************************** Templates ****************************/



	/**
	 * @param string|NULL $class
	 * @return Nette\Templates\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$templateFactory = $this->getPresenter()->getService('Kdyby\Templates\ITemplateFactory');
		return $templateFactory->createTemplate($this, $class);
	}



	/**************************** Components magic ****************************/



	/**
	 * @param string $name
	 * @return Nette\Component
	 */
	public function createComponent($name)
	{
		$component = parent::createComponent($name);
		return Kdyby\Component\Helpers::createComponent($this, $component, $name);
	}

}
