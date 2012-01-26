<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Config;

use Doctrine;
use Kdyby;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\UI\Presenter;
use Nette\Caching\Storages\FileStorage;
use Nette\DI\Container as NContainer;
use Nette\Diagnostics\Debugger;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Symfony;



// functions & exceptions
require_once __DIR__ . '/../functions.php';
require_once __DIR__ . '/../exceptions.php';

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Configurator extends Nette\Object
{

	/** @var array */
	public $parameters = array();

	/** @var boolean */
	private $initialized = FALSE;

	/** @var \Kdyby\Packages\PackagesContainer */
	private $packages;

	/** @var \Nette\DI\Container */
	private $container;



	/**
	 * @param array $parameters
	 * @param \Kdyby\Packages\IPackageList $packageFinder
	 */
	public function __construct($parameters = NULL, Kdyby\Packages\IPackageList $packageFinder = NULL)
	{
		// path defaults
		$this->parameters = static::defaultPaths($parameters);

		// debugger defaults
		static::setupDebugger($this->parameters);

		// finder
		$packageFinder = $packageFinder ?: new Kdyby\Packages\InstalledPackages($this->parameters['appDir']);
		$this->packages = new Kdyby\Packages\PackagesContainer($packageFinder);

		// environment
		$this->setProductionMode();
		$this->setEnvironment($this->parameters['productionMode'] ? 'prod' : 'dev');
	}



	/**
	 * @return \Nette\Config\Configurator
	 */
	protected function createConfigurator()
	{
		$config = new Nette\Config\Configurator();
		$config->addParameters($this->parameters);
		$config->setTempDirectory($this->parameters['tempDir']);
		return $config;
	}



	/**
	 * @param string $name
	 */
	public function setEnvironment($name)
	{
		$this->parameters['environment'] = $name;
		$this->parameters['consoleMode'] = $name === 'console' ?: PHP_SAPI === 'cli';
	}



	/**
	 * When given NULL, the production mode gets detected automatically
	 *
	 * @param bool|NULL $isProduction
	 */
	public function setProductionMode($isProduction = NULL)
	{
		$this->parameters['productionMode'] = is_bool($isProduction) ? $isProduction
			: Nette\Config\Configurator::detectProductionMode();
		$this->parameters['kdyby']['debug'] = !$this->parameters['productionMode'];
	}



	/**
	 */
	private function startup()
	{
		if ($this->initialized) {
			return;
		}

		// Last call for debugger
		static::setupDebuggerMode($this->parameters);

		// packages
		foreach ($this->packages as $name => $package) {
			$this->parameters['kdyby']['packages'][$name] = get_class($package);
		}

		// configure
		$configurator = $this->createConfigurator();

		// robot loader autoRebuild
		foreach (Nette\Loaders\AutoLoader::getLoaders() as $loader) {
			if ($loader instanceof Nette\Loaders\RobotLoader) {
				$loader->autoRebuild = !$this->parameters['productionMode'];
				$loader->setCacheStorage(new FileStorage($this->parameters['tempDir'] . '/cache'));
			}
		}

		// create container
		$configurator->onCompile[] = callback($this->packages, 'compile');
		$configurator->addConfig($this->getConfigFile(), Nette\Config\Configurator::NONE);
		$this->container = $configurator->createContainer();

		$this->initialized = TRUE;
	}



	/**
	 * @return string
	 */
	public function getConfigFile()
	{
		return $this->parameters['appDir'] . '/config/config_' . $this->parameters['environment'] . '.neon';
	}



	/**
	 * @return \Kdyby\Packages\PackagesContainer
	 */
	public function getPackages()
	{
		$this->startup();
		return $this->packages;
	}



	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public function getContainer()
	{
		$this->startup();
		return $this->container;
	}



	/********************* services *********************/



	/**
	 * @param \Kdyby\Doctrine\Diagnostics\Panel $panel
	 */
	public static function configureDbalSqlLogger(Kdyby\Doctrine\Diagnostics\Panel $panel)
	{
		$panel->registerBarPanel(Debugger::$bar);
	}



	/********************* service factories *********************/



	/**
	 * Prepares the absolute filesystem paths
	 *
	 * @param array|string $params
	 *
	 * @return array
	 */
	protected static function defaultPaths($params)
	{
		// public root
		if ($params === NULL) {
			$params = isset($_SERVER['SCRIPT_FILENAME']) ? dirname($_SERVER['SCRIPT_FILENAME']) : NULL;
		}

		if (!is_array($params)) {
			$params = array('wwwDir' => $params);
		}

		// application root
		if (!isset($params['appDir'])) {
			$params['appDir'] = realpath($params['wwwDir'] . '/../app');
		}

		// temp directory
		if (!isset($params['tempDir'])) {
			$params['tempDir'] = $params['appDir'] . '/temp';
		}

		// log directory
		if (!isset($params['logDir'])) {
			$params['logDir'] = $params['appDir'] . '/log';
		}

		return $params;
	}



	/**
	 * Setups the Debugger defaults
	 *
	 * @param array $params
	 */
	protected static function setupDebugger(array $params)
	{
		if (!is_dir($params['logDir'])) {
			@mkdir($params['logDir'], 0777);
		}

		if (!is_writable($params['logDir'])) {
			throw new Kdyby\DirectoryNotWritableException("Logging directory '" . $params['logDir'] . "' is not writable.");
		}

		Debugger::$logDirectory = $params['logDir'];
		Debugger::$strictMode = TRUE;
		Debugger::enable(Debugger::PRODUCTION);
	}



	/**
	 * @param array $params
	 */
	protected static function setupDebuggerMode(array $params)
	{
		Debugger::$productionMode = $params['productionMode'];
		Debugger::$consoleMode = $params['consoleMode'];
	}

}
