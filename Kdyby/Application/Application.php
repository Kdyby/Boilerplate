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

use Nette;
use Nette\Environment;



final class Application extends Nette\Application\Application
{
	/** @var string */
	public $errorPresenter = 'Error';



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
		// session setup
		Nette\Environment::getConfigurator()->setupSession($this->getSession());

		// template macros
		Nette\Environment::getConfigurator()->setupTemplateMacros();

//		$this->initializeModules();

//		$this->initializeContainer();

		$this->getContainer()->freeze();

		parent::run();
	}



	/**
	 * @return Kdyby\Injection\ServiceContainer
	 */
	public function getContainer()
	{
		return $this->getContext();
	}



	/**
	 * should return array of module loader instances
	 * @see /home/hosiplan/develop/libs/symfony/symfony-sandbox/app/AppKernel.php:8
	 */
//	abstract protected function registerModules();



	/**
	 * Should load and register all modules, pass them the containerBuilder, so that they can register themselfs, or their compiler
	 */
	private function initializeModules()
	{

	}



	/**
	 * container should be builded and cached
	 */
	private function initializeContainer()
	{
		
	}



	/**
	 * @return Kdyby\Application\Kdyby
	 */
	public function registerPanels()
	{
		//Panel\UserPanel::register();

		// develop environment!
		//\Kdyby\Debug\DoctrinePanel::register();

		return $this;
	}

}