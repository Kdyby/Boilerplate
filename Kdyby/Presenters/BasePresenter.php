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


namespace Kdyby\Presenters;

use Nette;
use Nette\Environment;
use Nette\Reflection\ClassReflection;
use Nette\String;
use Nette\Web\User;
use Kdyby;



/**
 * Base class for all application presenters.
 * @property-read Kdyby\Application\DatabaseManager $dtm
 * @property Kdyby\Templates\FileTemplate $template
 * @method Kdyby\Templates\FileTemplate getTemplate
 */
abstract class BasePresenter extends Nette\Application\Presenter
{

	/** @var Nette\ITranslator */
	private $translator;



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



	/*=========================== Redirecting =============================*/

	

	public function redirectLogin($i)
	{
		if ($this->getUser()->getLogoutReason() === User::INACTIVITY) {
			$this->flashMessage('You have been logged out due to inactivity. Please login again.');
		}

		$backlink = $this->getApplication()->storeRequest();
		$this->redirect('Auth:login', array('backlink' => $backlink));
	}


	public function redirectUnauthorized()
	{

	}


	public function redirectBack($defaultRedirect = Null)
	{
		$backlink = $this->backlink;
		$this->backlink = Null;

		$this->getApplication()->restoreRequest($backlink);
		if ($defaultRedirect !== Null) {
			$this->redirect($defaultRedirect);
		}
	}



	/*=========================== Templates =============================*/



	/**
	 * @param string $switch
	 * @return string
	 */
	public function getThemePath($switch = NULL)
	{
		static $themes = array();

		if (!isset($themes[$switch])) {
			$themes[$switch] = Kdyby\Templates\Helpers::getThemePath($this, $switch);
		}

		return $themes[$switch];
	}


}