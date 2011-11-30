<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Kdyby\Caching\CacheServices;
use Kdyby\Package\PackageManager;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Caching\Cache;
use Nette\DI\Container as NContainer;
use Nette\Diagnostics\Debugger;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Symfony;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;



// functions
require_once __DIR__ . '/../functions.php';

/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read Container $container
 * @property-read Kdyby\Application\Application $application
 */
class Configurator extends Nette\Object implements IConfigurator
{

	const CACHE_CONFIG_NS = 'Kdyby.Configuration';

	/** @var string */
	public $environment = 'prod';

	/** @var array */
	public $params = array();

	/** @var boolean */
	private $initialized = FALSE;

	/** @var array */
	private $packages;

	/** @var Container */
	private $container;

	/** @var CacheServices */
	private $cache;

	/** @var LoaderInterface */
	private $containerLoader;

	/** @var FileLoaderImportLogger */
	private $importsLogger;

	/** @var PackageManager */
	private $packageManager;

	/** @var \Kdyby\Package\IPackageList */
	private $packageFinder;



	/**
	 * @param array $params
	 * @param \Kdyby\Package\IPackageList $packageFinder
	 */
	public function __construct(array $params = NULL, \Kdyby\Package\IPackageList $packageFinder = NULL)
	{
		// path defaults
		$this->params = static::defaultPaths($params);

		// debugger defaults
		static::setupDebugger($params);

		// finder
		$this->packageFinder = $packageFinder ? : new Kdyby\Package\InstalledPackages($params['appDir']);

		// environment
		$this->setProductionMode();
		$this->setEnvironment($this->params['productionMode'] ? 'prod' : 'dev');
	}



	/**
	 * @param string $name
	 */
	public function setEnvironment($name)
	{
		$this->environment = $name;
		$this->params['environment'] = $name;
		$this->params['consoleMode'] = $name === 'console' ? : PHP_SAPI === 'cli';
	}



	/**
	 * When given NULL, the production mode gets detected automatically
	 *
	 * @param bool|NULL $isProduction
	 */
	public function setProductionMode($isProduction = NULL)
	{
		$this->params['productionMode'] = is_bool($isProduction) ? $isProduction
			: Nette\Config\Configurator::detectProductionMode();
	}



	/**
	 */
	private function startup()
	{
		if ($this->initialized) {
			return;
		}

		Debugger::$productionMode = $this->params['productionMode'];
		Debugger::$consoleMode = $this->params['consoleMode'];

		$this->initializePackages();
		$this->initializeContainer();

		$this->initialized = TRUE;
	}



	/********************* packages *********************/



	/**
	 * @return array
	 */
	public function getPackages()
	{
		$this->startup();
		return $this->packages;
	}



	/**
	 */
	private function initializePackages()
	{
		$packages = $this->packageFinder->getPackages();
		$this->packages = $this->getPackageManager()->activate($packages);
		foreach ($this->packages as $name => $package) {
			$this->params['kdyby.packages'][$name] = get_class($package);
		}
	}



	/**
	 * @return \Kdyby\Package\PackageManager
	 */
	public function getPackageManager()
	{
		if ($this->packageManager === NULL) {
			$this->packageManager = new PackageManager();
		}

		return $this->packageManager;
	}



	/********************* container *********************/



	/**
	 * @return Container
	 */
	public function getContainer()
	{
		$this->startup();
		return $this->container;
	}



	/**
	 * Initializes the service container.
	 *
	 * The cached version of the service container is used when fresh, otherwise the
	 * container is built.
	 */
	private function initializeContainer()
	{
		$class = $this->getContainerClass();
		$key = array($this->environment, $class);

		// for caching ContainerClass
		$cache = $this->getCache()->create(self::CACHE_CONFIG_NS, TRUE);

		// try to load cache
		$cached = $cache->load($key);
		if ($cached) {
			require $cached['file'];
			fclose($cached['handle']);

		} else {
			// build container class
			$container = $this->buildContainer();
			$classDump = $this->dumpContainer($container, $class) . "\n\n";

			// prepare Nette environment
			$nContainer = new Nette\DI\Container;
			$nContainer->params['tempDir'] = $this->params['tempDir'];
			$classDump .= $this->checkTempDir($this->params['tempDir']);

			// save definition
			$cache->save($key, $classDump, array(
				Cache::FILES => array_merge((array)$this->getConfigFile(), $this->getImportedFiles())
			));
			Nette\Utils\LimitedScope::evaluate($classDump);
		}

		// initialize container
		$this->container = new $class();
		$this->container->setCacheServices($this->getCache());
		$this->container->set('application.package_manager', $this->getPackageManager());
	}



	/**
	 * Gets the container class.
	 *
	 * @return string The container class
	 */
	protected function getContainerClass()
	{
		$name = preg_replace('/[^a-zA-Z0-9_]+/', '', basename($this->params['appDir']));
		return ucfirst($name) . ucfirst($this->environment) . 'ProjectContainer';
	}



	/**
	 * Builds the service container.
	 *
	 * @return ContainerBuilder The compiled service container
	 */
	private function buildContainer()
	{
		foreach (array('logs' => $this->params['logDir']) as $name => $dir) {
			if (!is_dir($dir) || !is_writable($dir)) {
				throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", $name, $dir));
			}
		}

		// create new container
		$container = new ContainerBuilder(new ParameterBag($this->params));

		// build packages
		$extensions = array();
		foreach ($this->packages as $package) {
			$package->build($container);

			if ($extension = $package->getContainerExtension()) {
				$container->registerExtension($extension);
				$extensions[] = $extension->getAlias();
			}
		}

		// ensure these extensions are implicitly loaded
		if ($extensions) {
			$container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
		}

		// merge configuration file
		$this->mergeConfigFile($container);

		// compile
		$container->compile();
		return $container;
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	protected function mergeConfigFile(ContainerBuilder $container)
	{
		if (!file_exists($this->getConfigFile())) {
			return;
		}

		$cont = $this->getContainerLoader($container)->load($this->getConfigFile());
		if ($cont !== NULL) {
			$container->merge($cont);
		}
	}



	/**
	 * @return string
	 */
	protected function getConfigFile()
	{
		return $this->params['appDir'] . '/config/config_' . $this->environment . '.neon';
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container The service container
	 * @param string $class
	 *
	 * @return string
	 */
	private function dumpContainer(ContainerBuilder $container, $class)
	{
		$dumper = new PhpDumper($container);
		return $dumper->dump(array('class' => $class, 'base_class' => 'Kdyby\DI\SystemContainer'));
	}



	/**
	 * @return CacheServices
	 */
	private function getCache()
	{
		if ($this->cache === NULL) {
			$this->cache = new CacheServices($this->params['tempDir']);
		}

		return $this->cache;
	}



	/**
	 * @return FileLoaderImportLogger
	 */
	private function getImportsLogger()
	{
		if ($this->importsLogger === NULL) {
			$this->importsLogger = new FileLoaderImportLogger();
		}

		return $this->importsLogger;
	}



	/**
	 * @return array
	 */
	protected function getImportedFiles()
	{
		return array_map(function ($call)
		{
			return dirname($call['sourceResource']) . '/' . $call['resource'];
		}, $this->getImportsLogger()->getCalls());
	}



	/**
	 * Returns a loader for the container.
	 *
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container The service container
	 *
	 * @return \Symfony\Component\Config\Loader\LoaderInterface
	 */
	private function getContainerLoader(ContainerInterface $container)
	{
		if ($this->containerLoader === NULL) {
			$locator = new FileLocator($this->params['appDir'] . '/config');

			$resolver = new LoaderResolver(array(
				$neonLoader = new Loader\NeonFileLoader($container, $locator),
				$iniLoader = new Loader\IniFileLoader($container, $locator),
				$yamlLoader = new Loader\YamlFileLoader($container, $locator),
			));

			$neonLoader->setLogger($this->getImportsLogger());
			$iniLoader->setLogger($this->getImportsLogger());
			$yamlLoader->setLogger($this->getImportsLogger());

			$this->containerLoader = new DelegatingLoader($resolver);
		}

		return $this->containerLoader;
	}



	/********************* services *********************/



	/**
	 * @param \Nette\Application\Application $application
	 */
	public static function configureApplication(Nette\Application\Application $application)
	{
		if (Presenter::$invalidLinkMode === NULL) {
			Presenter::$invalidLinkMode = Debugger::$productionMode
				? Presenter::INVALID_LINK_SILENT
				: Presenter::INVALID_LINK_WARNING;
		}

		$application->catchExceptions = Debugger::$productionMode;
	}



	/**
	 * @param \Nette\Loaders\RobotLoader $robot
	 */
	public static function configureRobotLoader(Nette\Loaders\RobotLoader $robot)
	{
		$robot->autoRebuild = $robot->autoRebuild ? !Debugger::$productionMode : FALSE;
	}



	/**
	 * @param \Kdyby\Doctrine\Diagnostics\Panel $panel
	 */
	public static function configureDbalSqlLogger(Kdyby\Doctrine\Diagnostics\Panel $panel)
	{
		$panel->registerBarPanel(Debugger::$bar);
	}



	/**
	 * @param Nette\Application\IRouter $router
	 */
	public static function configureRouter(Nette\Application\IRouter $router)
	{
		$router[] = new Nette\Application\Routers\Route('index.php', 'Homepage:default', Nette\Application\Routers\Route::ONE_WAY);
		$router[] = new Nette\Application\Routers\Route('<presenter>/<action>[/<id>]', 'Homepage:default');
	}



	/********************* service factories ****************d*g**/



	/**
	 * @param string $tempDir
	 *
	 * @throws \Nette\InvalidStateException
	 * @return string
	 */
	private function checkTempDir($tempDir)
	{
		$code = '';
		$dir = $tempDir . '/cache';
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists

		// checks whether directory is writable
		$uniq = uniqid('_', TRUE);
		umask(0000);
		if (!@mkdir("$dir/$uniq", 0777)) { // @ - is escalated to exception
			throw new Nette\InvalidStateException("Unable to write to directory '$dir'. Make this directory writable.");
		}

		// tests subdirectory mode
		$useDirs = @file_put_contents("$dir/$uniq/_", '') !== FALSE; // @ - error is expected
		@unlink("$dir/$uniq/_");
		@rmdir("$dir/$uniq"); // @ - directory may not already exist

		return 'Nette\Caching\Storages\FileStorage::$useDirectories = ' . ($useDirs ? 'TRUE' : 'FALSE') . ";\n";
	}



	/**
	 * Prepares the absolute filesystem paths
	 *
	 * @param array|string $params
	 *
	 * @return array
	 */
	public static function defaultPaths($params = NULL)
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
	public static function setupDebugger(array $params)
	{
		if (!is_dir($params['logDir'])) {
			@mkdir($params['logDir'], 0777);
		}

		if (!is_writable($params['logDir'])) {
			throw new Nette\IOException("Logging directory '" . $params['logDir'] . "' is not writable.");
		}

		Debugger::$logDirectory = $params['logDir'];
		Debugger::$strictMode = TRUE;
		Debugger::enable(Debugger::PRODUCTION);
	}

}
