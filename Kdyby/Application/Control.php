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
 * @property Kdyby\Templates\FileTemplate $template
 * @method Kdyby\Templates\FileTemplate getTemplate
 */
class Control extends Nette\Application\Control
{

	/** @var Nette\ITranslator */
	private $translator;

	/** @var Doctrine\ORM\EntityManager */
	private $em;



	/**
	 * @return Nette\Web\IUser
	 */
	public function getUser()
	{
		return Nette\Environment::getUser();
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		if ($this->em === NULL) {
			$this->em = Environment::getService('Doctrine\ORM\EntityManager');
		}

		return $this->em;
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


	protected function createTemplate($class = NULL)
	{
		$templateFactory = new Kdyby\Templates\TemplateFactory($this);
		return $templateFactory->createTemplate($class);
	}



	/*=========================== Components magic =============================*/



	public function createComponent($name)
	{
		$component = parent::createComponent($name);
		return Kdyby\Component\Helpers::createComponent($this, $component, $name);
	}

}
