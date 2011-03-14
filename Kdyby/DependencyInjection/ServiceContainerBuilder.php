<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Kdyby\DependencyInjection;

use Kdyby;
use Nette;
use Nette\Environment;
use Nette\Config\Config;



/**
 * ServiceContainer builder
 *
 * @author	Patrik Votoček
 * @author	David Grudl
 *
 * @property-write string $ServiceContainerClass
 * @property-read IServiceContainer $ServiceContainer
 */
class ServiceContainerBuilder extends Nette\Configurator
{

	/** @var string */
	private static $kdybyConfigFile = "%kdybyDir%/config.kdyby.neon";

	/** @var string */
	private $serviceContainerClass = 'Kdyby\DependencyInjection\ServiceContainer';

	/** @var array */
	private $autoRunServices = array();

	/** @var array */
	private $configFiles = array();



	public function __construct()
	{
		foreach (array(self::$kdybyConfigFile, $this->defaultConfigFile) as $file) {
			$file = realpath(Nette\Environment::expand($file));
			if (file_exists($file)) {
				$this->configFiles[$file] = array($file, TRUE, array());
			}
		}

		Kdyby\Templates\KdybyMacros::register();
	}



	/**
	 * @param string $file
	 * @param bool $environment
	 * @param string|array $prefixPath
	 * @return ServiceContainerBuilder
	 */
	public function addConfigFile($file, $environments = TRUE, $prefixPath = NULL)
	{
		$file = realpath(Nette\Environment::expand($file));
		$this->configFiles[$file] = array($file, (bool)$environments, $prefixPath ? (array)$prefixPath : array());
		return $this;
	}



	/**
	 * @param string
	 * @return ServiceContainerBuilder
	 * @throws \InvalidArgumentException
	 */
	public function setServiceContainerClass($class)
	{
		if (!class_exists($class)) {
			throw new \InvalidArgumentException("ServiceContainer class '$class' does not exist");
		}

		$ref = new Nette\Reflection\ClassReflection($class);
		if (!$ref->implementsInterface('Kdyby\DependencyInjection\IServiceContainer')) {
			throw new \InvalidArgumentException("ServiceContainer class '$class' is not valid 'Kdyby\DependencyInjection\IServiceContainer'");
		}

		$this->serviceContainerClass = $class;
		return $this;
	}



	/**
	 * @return IServiceContainer
	 */
	public function getServiceContainer()
	{
		return Environment::getContext();
	}



	/**
	 * @param string
	 */
	protected function loadEnvironmentName($name)
	{
		Environment::setVariable('environment', $name);
		$this->getServiceContainer()->setEnvironment($name);
	}



	/**
	 * Detect environment mode.
	 * 
	 * @param  string mode name
	 * @return bool
	 */
	public function detect($name)
	{
		switch ($name) {
			case 'production':
				// detects production mode by server IP address
				if (isset($_SERVER['SERVER_ADDR']) || isset($_SERVER['LOCAL_ADDR'])) {
					$addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
					if (substr($addr, -4) === '.loc') {
						return FALSE;
					}
				}
		}

		return parent::detect($name);
	}



	/**
	 * @param Nette\Config\Config
	 * @throws \NotSupportedException
	 */
	protected function loadIni(Config $config)
	{
		if (PATH_SEPARATOR !== ';' && isset($config->include_path)) {
			$config->include_path = str_replace(';', PATH_SEPARATOR, $config->include_path);
		}

		foreach (clone $config as $key => $value) { // flatten INI dots
			if ($value instanceof Config) {
				unset($config->$key);
				foreach ($value as $k => $v) {
					$config->{"$key.$k"} = $v;
				}
			}
		}

		foreach ($config as $key => $value) {
			if (!is_scalar($value)) {
				throw new \InvalidStateException("Configuration value for directive '$key' is not scalar.");
			}

			if ($key === 'date.timezone') { // PHP bug #47466
				date_default_timezone_set($value);
			}

			self::iniSet($key, $value);
		}
	}



	/**
	 * @param string $key
	 * @param mixed $value
	 */
	protected static function iniSet($key, $value)
	{
		if (function_exists('ini_set')) {
			ini_set($key, $value);

		} else {
			switch ($key) {
				case 'include_path':
					set_include_path($value);
					break;

				case 'iconv.internal_encoding':
					iconv_set_encoding('internal_encoding', $value);
					break;

				case 'mbstring.internal_encoding':
					mb_internal_encoding($value);
					break;

				case 'date.timezone':
					date_default_timezone_set($value);
					break;

				case 'error_reporting':
					error_reporting($value);
					break;

				case 'ignore_user_abort':
					ignore_user_abort($value);
					break;

				case 'max_execution_time':
					set_time_limit($value);
					break;

				default:
					if (ini_get($key) != $value) { // intentionally ==
						throw new \NotSupportedException('Required function ini_set() is disabled.');
					}
			}
		}
	}



	/**
	 * @param Nette\Config\Config $config
	 */
	protected function loadParameters(Config $config)
	{
		foreach ($config as $key => $value) {
			if ($key == "variables" && $value instanceof Config) {
				foreach ($value as $k => $v) {
					$this->getServiceContainer()->setParameter($k, $v);
					Environment::setVariable($k, $v);
				}

			} elseif ($key != "php" && $key != "services") {
				$tmp = $value instanceof Config ? $value->toArray() : $value;
				$this->getServiceContainer()->setParameter($key, $tmp);
			}
		}
	}



	/**
	 * @param array
	 */
	protected function loadServices(array $config)
	{
		foreach ($config as $name => $data) {
			$service = key_exists('class', $data) ? $data['class'] : (key_exists('factory', $data) ? $data['factory'] : NULL);

			$this->getServiceContainer()->addService($name, $service, key_exists('singleton', $data) ? $data['singleton'] : TRUE, $data);

			if (key_exists('run', $data) && $data['run']) {
				$this->autoRunServices[] = $name;
			}
		}
	}



	/**
	 * @param Nette\Config\Config $config
	 */
	protected function loadConstants(Config $config)
	{
		foreach ($config as $key => $value) {
			define($key, $value);
		}
	}



	/**
	 * @param Nette\Config\Config $config
	 */
	protected function loadModes(Config $config)
	{
		foreach($config as $mode => $state) {
			Environment::setMode($mode, $state);
		}
	}



	/**
	 * @return void
	 */
	protected function autoRunServices()
	{
		foreach ($this->autoRunServices as $serviceName) {
			$this->getServiceContainer()->getService($serviceName);
		}

		$this->autoRunServices = array();
	}



	/**
	 * @return Nette\Config\Config
	 */
	protected function loadConfigs()
	{
		$name = Environment::getName();
		$configs = array();

		// read and return according to actual environment name
		foreach ($this->configFiles as $file => $config) {
			$configs[$file] = Nette\Config\Config::fromFile(Nette\Environment::expand($config[0]), $config[1] ? $name : NULL);
		}

		$mergedConfig = array();
		foreach ($this->configFiles as $file => $config) {
			$appendConfig = array();

			$prefixed = &$appendConfig;
			foreach ($config[2] as $prefix) {
				$prefixed = &$prefixed[$prefix];
			}
			$prefixed = $configs[$file]->toArray();

			$mergedConfig = array_replace_recursive($mergedConfig, $appendConfig);
		}

		return new Nette\Config\Config($mergedConfig);
	}



	/**
	 * Loads global configuration from file and process it.
	 * @param  string|Nette\Config\Config  file name or Config object
	 * @return Nette\Config\Config
	 *
	 * @author Patrik Votoček
	 */
	public function loadConfig($file)
	{
		if ($file) {
			$this->addConfigFile($file);
		}

		$config = $this->loadConfigs();

		isset($config->php) && $this->loadIni($config->php);

		$this->loadParameters($config);
		// $this->loadServices($this->defaultServices); // TODO: why??

		isset($config->services) && $this->loadServices($config->services->toArray());
		isset($config->const) && $this->loadConstants($config->const);
		isset($config->mode) && $this->loadModes($config->mode);

		$this->autoRunServices();

		return $config;
	}



	/******************************************** FACTORIES **************************************************/

	public $defaultServices = array(
		'Nette\\Application\\Application' => array(
			'factory' => array(__CLASS__, 'createApplication'),
			'arguments' => array('%Application%'),
			'aliases' => array('application'),
		),
		'Nette\\Web\\HttpContext' => array(
			'class' => 'Nette\Web\HttpContext',
			'aliases' => array('httpContext'),
		),
		'Nette\\Web\\IHttpRequest' => array(
			'factory' => array(__CLASS__, 'createHttpRequest'),
			'aliases' => array('httpRequest'),
		),
		'Nette\\Web\\IHttpResponse' => array(
			'class' => 'Nette\Web\HttpResponse',
			'aliases' => array('httpResponse'),
		),
		'Nette\\Caching\\ICacheStorage' => array(
			'factory' => array(__CLASS__, 'createCacheStorage'),
		),
		'Nette\\Caching\\ICacheJournal' => array(
			'factory' => array(__CLASS__, 'createCacheJournal'),
		),
		'Nette\\Mail\\IMailer' => array(
			'factory' => array(__CLASS__, 'createMailer'),
			'aliases' => array('mailer'),
		),
		'Nette\\Web\\Session' => array(
			'class' => array(__CLASS__, 'createSession'),
			'arguments' => array('@Nette\\Web\\IHttpRequest'),
			'aliases' => array('sessionStorage'),
		),
		'Nette\\Loaders\\RobotLoader' => array(
			'factory' => array(__CLASS__, 'createRobotLoader'),
		),
		'Nette\\Caching\\Cache' => array(
			'class' => 'Nette\\Caching\\Cache',
			'arguments' => array('@Nette\\Caching\\ICacheStorage', 'Nette'),
			'aliases' => array('cache'),
		),
		'Nette\\Security\\IAuthenticator' => array(
			'class' => 'Kdyby\\Security\\Authenticator',
			'arguments' => array('@Doctrine\\ORM\\EntityManager', '%Security%'),
			'aliases' => array('authenticator'),
		),
		'Nette\\Security\\IAuthorizator' => array(
			'class' => 'Kdyby\\Security\Authorizator',
			'arguments' => array('@Doctrine\\ORM\\EntityManager'),
			'aliases' => array('authorizator'),
		),
		'Nette\\Web\\IUser' => array(
			'class' => 'Kdyby\\Security\\User',
			'aliases' => array('user'),
		),
		'Nette\\Application\\IRouter' => array(
			'class' => 'Nette\\Application\\MultiRouter'
		),
		'Nette\\Application\\IPresenterFactory' => array(
			'class' => 'Kdyby\\Application\\PresenterFactory',
			'arguments' => array('@Kdyby\\Registry\\NamespacePrefixes'),
		),
		'Nette\\Caching\\IMemcacheStorage' => array(
			'factory' => array(__CLASS__, 'createMemcacheStorage'),
			'arguments' => array(array('prefix' => 'Kdyby.')),
			'aliases' => array('memcache'),
		),
		'Nette\\Caching\\IMemcacheJournal' => array(
			'factory' => array(__CLASS__, 'createMemcacheJournal'),
		),

		'Doctrine\\ORM\\EntityManager' => array(
			'factory' => array('Kdyby\\Doctrine\\ServiceFactory', 'createEntityManager'),
			'arguments' => array('%Database%', '@Doctrine\\ORM\\Configuration', '@Doctrine\\Common\\EventManager'),
			'aliases' => array('entityManager'),
		),
		'Doctrine\\Common\\Cache\\Cache' => array(
			'class' => 'Kdyby\\Doctrine\\Cache',
			'arguments' => array('@Kdyby\\Doctrine\\Cache'),
		),
		'Doctrine\\ORM\\Configuration' => array(
			'factory' => array('Kdyby\\Doctrine\\ServiceFactory', 'createConfiguration'),
			'arguments' => array('@Doctrine\\Common\\Cache\\Cache', '%EntityDirs%'),
			'methods' => array(
				array('method' => 'setMetadataCacheImpl', 'arguments' => array('@Doctrine\Common\Cache\Cache')),
				array('method' => 'setQueryCacheImpl', 'arguments' => array('@Doctrine\Common\Cache\Cache')),
			),
		),
		'Doctrine\\Common\\EventManager' => array(
			'class' => 'Doctrine\\Common\\EventManager',
		),
//		set through profiler parameter in %Database% parameter
//		'Doctrine\\DBAL\\Logging\\SQLLogger' => array(
//			'factory' => array('Kdyby\\Doctrine\\Panel', 'create'),
//		),

		'Kdyby\\Doctrine\\Cache' => array(
			'class' => 'Nette\\Caching\\Cache',
			'arguments' => array('@Nette\\Caching\\ICacheStorage', 'Doctrine'),
		),
		'Kdyby\\Registry\\NamespacePrefixes' => array(
			'factory' => array(__CLASS__, 'createRegistryNamespacePrefixes'),
			'methods' => array(
				array('method' => 'freeze'),
			),
			'aliases' => array('namespacePrefixes'),
		),
		'Kdyby\\Registry\\TemplateDirs' => array(
			'factory' => array(__CLASS__, 'createRegistryTemplateDirs'),
			'methods' => array(
				array('method' => 'freeze'),
			),
			'aliases' => array('templateDirs'),
		),
	);



	/**
	 * Get initial instance of ServiceContainer
	 *
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function createServiceContainer()
	{
		$serviceContainer = new $this->serviceContainerClass;
		foreach ($this->defaultServices as $name => $service) {
			$serviceContainer->addService($name, $service);
		}

		return $serviceContainer;
	}



	/**
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function createContext()
	{
		return $this->createServiceContainer();
	}



	/**
	 * @return Kdyby\Application\Application
	 */
	public static function createApplication(array $options = NULL)
	{
		if (Environment::getVariable('baseUri', NULL) === NULL) {
			Environment::setVariable('baseUri', Environment::getHttpRequest()->getUri()->getBaseUri());
		}

		$class = $parameters['application.class'];

		$ref = Kdyby\Reflection\ServiceReflection::from($class);
		$params = $ref->getConstructorParamClasses();
		$serviceContainer = clone $this->getServiceContainer();

		$application = $params ? $ref->newInstanceArgs($serviceContainer->expandParams($params)) : new $class;
		$application->setServiceContainer($serviceContainer);
		$application->catchExceptions = Environment::isProduction();

		return $application;
	}



	/**
	 * @author Patrik Votoček
	 *
	 * @return FreezableArray
	 */
	public static function createRegistryNamespacePrefixes()
	{
		$register = new Kdyby\Tools\FreezableArray();
		$register['app'] = 'App\\';
		$register['framework'] = 'Kdyby\\';

		return $register;
	}



	/**
	 * @author Patrik Votoček
	 * 
	 * @return FreezableArray
	 */
	public static function createRegistryTemplateDirs()
	{
		$register = new Kdyby\Tools\FreezableArray();
		$register['app'] = APP_DIR;
		$register['framework'] = KDYBY_DIR;

		return $register;
	}



	/**
	 * @return Nette\Application\MultiRouter
	 */
	public function createRouter()
	{
		$router = new Nette\Application\MultiRouter;
		$router[] = new Kdyby\Application\AdminRouter;

		return $router;
	}



	/**
	 * @param Nette\Web\Session $session
	 */
	public static function createSession(Nette\Web\HttpRequest $httpRequest)
	{
		$session = new Nette\Web\Session;

		// setup session
		if (!$session->isStarted()) {
			if (!Nette\Environment::isConsole()){
				$domainMap = (object)Nette\String::match($httpRequest->uri->host, Kdyby\Web\HttpHelpers::DOMAIN_PATTERN);
				$session->setCookieParams('/', '.' . $domainMap->domain);
			}

			$session->setExpiration(Nette\Tools::YEAR);
			if (!$session->exists()) {
				$session->start();
			}
		}

		return $session;
	}



	/**
	 * @return Nette\Caching\FileJournal
	 */
	public static function createCacheStorage()
	{
		$dir = Kdyby\Tools\FileSystem::prepareWritableDir('%varDir%/cache');

		$journal = Environment::getService('Nette\\Caching\\ICacheJournal');
		return new Kdyby\Caching\FileStorage($dir, $journal);
	}



	/**
	 * @return Nette\Caching\MemcachedStorage
	 */
	public static function createMemcacheStorage($options)
	{
		$config = Environment::getConfig('memcache');

		$journal = Environment::getService('Nette\Caching\IMemcacheJournal');
		return new Nette\Caching\MemcachedStorage($config['host'], $config['port'], $options['prefix'], $journal);
	}



	/**
	 * @return Nette\Caching\ICacheJournal
	 */
	public static function createMemcacheJournal()
	{
		/*if (Nette\Caching\SqliteJournal::isAvailable()) {
			return new Nette\Caching\SqliteJournal(Environment::getVariable('tempDir') . '/cachejournal.db');
		} else*/ {
			$dir = Kdyby\Tools\FileSystem::prepareWritableDir('%tempDir%/memcache');
			return new Nette\Caching\FileJournal($dir);
		}
	}

}
