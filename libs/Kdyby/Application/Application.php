<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method onStartup(Application $sender)
 * @method onShutdown(Application $sender, \Exception $e = NULL)
 * @method onRequest(Application $sender, \Nette\Application\Request $request)
 * @method onResponse(Application $sender, \Nette\Application\IResponse $response)
 * @method onError(Application $sender, \Exception $e)
 */
class Application extends Nette\Application\Application
{

	/**
	 * @var \Kdyby\Config\Configurator
	 */
	private $configurator;

	/**
	 * @var \Kdyby\Packages\PackageManager
	 */
	private $packageManager;

	/**
	 * @var \Kdyby\Packages\PackagesContainer
	 */
	private $packages;



	/**
	 * @param array|string|\Nette\Config\Configurator $params
	 * @param string $environment
	 * @param string $productionMode
	 */
	public function __construct($params = NULL, $environment = NULL, $productionMode = NULL)
	{
		if ($params instanceof Kdyby\Config\Configurator) {
			$this->configurator = $params;

		} else {
			$this->configurator = $this->createConfigurator($params);
		}

		// environment
		if ($environment !== NULL) {
			$this->configurator->setEnvironment($environment);
		}

		// production mode
		if ($productionMode !== NULL) {
			$this->configurator->setDebugMode(!$productionMode);
		}

		// inject application instance
		$container = $this->configurator->getContainer();
		$container->configureService('application', $this);

		// dependencies
		$this->initialize($container);

		// wire events
		$this->packages = $this->configurator->getPackages();
		$this->packages->setContainer($container);
		$this->packages->attach($this);

		// activate packages
		$this->packageManager->setActive($this->packages);
	}



	/**
	 * @param \Nette\DI\Container|\SystemContainer $container
	 */
	protected function initialize(Nette\DI\Container $container)
	{
		$this->packageManager = $container->kdyby->packageManager;

		parent::__construct(
			$container->nette->presenterFactory,
			$container->router,
			$container->httpRequest,
			$container->httpResponse,
			$container->session
		);
	}



	/**
	 * When debugger is not in production mode, call ->debug() on packages
	 */
	public function run()
	{
		if (Debugger::$productionMode === FALSE) {
			$this->packages->debug();
		}

		parent::run();
	}



	/**
	 * @param array $params
	 *
	 * @return \Kdyby\Config\Configurator
	 */
	protected function createConfigurator($params)
	{
		return new Kdyby\Config\Configurator($params);
	}



	/**
	 * @return \Kdyby\Config\Configurator
	 */
	public function getConfigurator()
	{
		return $this->configurator;
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
