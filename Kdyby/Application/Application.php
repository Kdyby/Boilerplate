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



final class Application extends Nette\Application\Application
{
	/** @var string */
	public $errorPresenter = 'Error';

	/** @var Kdyby\DependencyInjection\IServiceContainer */
	private $serviceContainer;



	/**
	 * @param Kdyby\DependencyInjection\IServiceContainer $serviceContainer
	 */
	public function setServiceContainer(Kdyby\DependencyInjection\IServiceContainer $serviceContainer)
	{
		$this->serviceContainer = $serviceContainer;
		$this->setContext($serviceContainer);
	}



	/**
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function getServiceContainer()
	{
		return $this->serviceContainer;
	}



	/**
	 * Dispatch a HTTP request to a front controller.
	 * @return void
	 */
	public function run()
	{
//		$this->initializeModules();

//		$this->initializeContainer();

		$this->getServiceContainer()->freeze();

		parent::run();
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
	 * @return Kdyby
	 */
	public function registerPanels()
	{
		//Panel\UserPanel::register();

		// develop environment!
		//\Kdyby\Debug\DoctrinePanel::register();

		return $this;
	}

}