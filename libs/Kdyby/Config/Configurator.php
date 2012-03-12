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
use Kdyby\Packages\IPackageList;
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
	public $parameters = array(
		'email' => NULL,
	);

	/** @var boolean */
	private $initialized = FALSE;

	/** @var \Kdyby\Packages\PackagesContainer */
	private $packages;

	/** @var \Nette\DI\Container */
	private $container;



	/**
	 * @param array $parameters
	 * @param \Kdyby\Packages\IPackageList $packages
	 *
	 * @throws \Kdyby\DirectoryNotWritableException
	 */
	public function __construct($parameters = NULL, IPackageList $packages = NULL)
	{
		// path defaults
		$this->parameters = static::defaultPaths($parameters) + $this->parameters;

		// check if temp dir is writable
		if (!is_writable($this->parameters['tempDir'])) {
			throw new Kdyby\DirectoryNotWritableException("Temp directory '" . $this->parameters['tempDir'] . "' is not writable.");
		}

		// debugger defaults
		$this->setupDebugger(array('productionMode' => TRUE, 'consoleMode' => PHP_SAPI === 'cli'));

		// finder
		$packages = $packages ?: new Kdyby\Packages\InstalledPackages($this->parameters['appDir']);
		$this->packages = new Kdyby\Packages\PackagesContainer($packages);

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
	 *
	 * @return \Kdyby\Config\Configurator
	 */
	public function setEnvironment($name)
	{
		$this->parameters['environment'] = $name;
		$this->parameters['consoleMode'] = $name === 'console' ?: PHP_SAPI === 'cli';
		return $this;
	}



	/**
	 * When given NULL, the production mode gets detected automatically
	 *
	 * @param bool|NULL $value
	 *
	 * @return \Kdyby\Config\Configurator
	 */
	public function setProductionMode($value = NULL)
	{
		$this->parameters['productionMode'] = is_bool($value) ? $value
			: Nette\Config\Configurator::detectProductionMode($value);

		$this->parameters['kdyby']['debug'] = !$this->parameters['productionMode'];
		return $this;
	}



	/**
	 */
	private function startup()
	{
		if ($this->initialized) {
			return;
		}

		// Last call for debugger
		$this->setupDebugger();

		// packages
		foreach ($this->packages as $name => $package) {
			$this->parameters['kdyby']['packages'][$name] = get_class($package);
		}

		// configure
		$configurator = $this->createConfigurator();

		// robot loader autoRebuild
		foreach (Nette\Loaders\AutoLoader::getLoaders() as $loader) {
			if ($loader instanceof Nette\Loaders\RobotLoader) {
				/** @var \Nette\Loaders\RobotLoader $loader */
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
		$appDir = $this->parameters['appDir'];
		$environment = $this->parameters['environment'];

		if (is_file($config = "$appDir/config.neon")) {
			return $config;

		} elseif (is_file($config = "$appDir/config/config_$environment.neon")) {
			return $config;

		} elseif (is_file($config = "$appDir/config/config.neon")) {
			return $config;
		}
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
	 *
	 * @throws \Kdyby\DirectoryNotWritableException
	 */
	protected function setupDebugger($params = array())
	{
		$params = $params + $this->parameters;
		if (!is_dir($logDir = $params['logDir'])) {
			@mkdir($logDir, 0777);
		}

		// check if log dir is writable
		if (!is_writable($logDir)) {
			throw new Kdyby\DirectoryNotWritableException("Logging directory '" . $logDir . "' is not writable.");
		}

		Debugger::$strictMode = TRUE;
		Debugger::enable($params['productionMode'], $logDir, $params['email']);
		Debugger::$consoleMode = $params['consoleMode'];
	}



	/**
	 * @param string $appDir
	 * @param string $environment
	 *
	 * @throws \Kdyby\IOException
	 * @return \Kdyby\Config\Configurator
	 */
	public static function scriptInit($appDir, $environment = 'console')
	{
		if (!is_dir($appDir)) {
			throw new Kdyby\IOException("Given path is not a directory.");
		}

		// arguments
		$conf = new static(array(
			'appDir' => $appDir,
			'wwwDir' => $appDir . '/../www',
		));

		$conf->setEnvironment($environment);
		$conf->setProductionMode(TRUE);
		return $conf;
	}

}
