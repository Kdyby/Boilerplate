<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Application extends Nette\Application\Application
{

	/** @var \Kdyby\DI\IConfigurator */
	private $configurator;

	/** @var \Kdyby\Application\RequestManager */
	private $requestsManager;

	/** @var \Kdyby\Package\PackageManager */
	private $packageManager;



	/**
	 * @param array|string|\Kdyby\DI\IConfigurator $params
	 * @param string $environment
	 * @param string $productionMode
	 */
	public function __construct($params = NULL, $environment = NULL, $productionMode = NULL)
	{
		if ($params instanceof Kdyby\DI\IConfigurator) {
			$this->configurator = $params;
			$params = $this->configurator->params;

		} else {
			$this->configurator = $this->createConfigurator($params);
		}

		// environment
		if ($environment !== NULL) {
			$this->configurator->setEnvironment($environment);
		}

		// production mode
		if ($productionMode !== NULL) {
			$this->configurator->setProductionMode($productionMode);
		}

		// inject application instance
		$container = $this->configurator->getContainer();
		$container->set('application', $this);

		// wire events
		$invoker = $container->get('package.manager')->createInvoker();
		$invoker->setContainer($container);
		$invoker->attach($this);

		// dependencies
		$this->packageManager = $container->get('application.package_manager');
		$this->requestsManager = $container->get('application.stored_requests_manager');
		parent::__construct(
			$container->get('application.presenter_factory'),
			$container->get('application.router'),
			$container->get('http.request'),
			$container->get('http.response'),
			$container->get('http.session')
		);
	}



	/**
	 * @param array $params
	 *
	 * @return \Kdyby\DI\IConfigurator
	 */
	protected function createConfigurator(array $params)
	{
		return new Kdyby\DI\Configurator($params);
	}



	/**
	 * @return \Kdyby\DI\IConfigurator
	 */
	protected function getConfigurator()
	{
		return $this->configurator;
	}



	/********************* Request serialization *********************/



	/**
	 * Stores current request to session.
	 *
	 * @param string $expiration
	 *
	 * @return string
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		return $this->requestsManager->storeCurrentRequest($expiration);
	}



	/**
	 * Restores current request to session.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$this->requestsManager->restoreRequest($key);
	}



	/********************* Packages *********************/



	/**
	 * Checks if a given class name belongs to an active package.
	 *
	 * @param string $class
	 *
	 * @return boolean
	 */
	public function isClassInActivePackage($class)
	{
		return $this->packageManager->isClassInActivePackage($class);
	}



	/**
	 * @see \Kdyby\Package\PackageManager::locateResource()
	 *
	 * @param string $name  A resource name to locate
	 *
	 * @return string|array
	 */
	public function locateResource($name)
	{
		return $this->packageManager->locateResource($name);
	}

}
