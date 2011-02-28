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


namespace Kdyby\Environment;

use Nette;
use Nette\Environment;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Configurator extends Nette\Configurator
{

	/** @var string */
	private static $kdybyConfigFile = "%kdybyDir%/config.kdyby.neon";

	/** @var array */
	private static $configHooks = array(
		"Nette-Security-IIdentity" => "Kdyby\\Identity"
	);



	public function __construct()
	{
		// session setup
		$this->setupSession(Environment::getSession());

		// templates
		Kdyby\Templates\KdybyMacros::register();
	}



	/**
	 * @param Nette\IContext $context
	 * @return Kdyby\Application\PresenterFactoryChain
	 */
	public static function createPresenterFactory(Nette\IContext $context)
	{
		$presenterFactoryChain = new Kdyby\Application\PresenterFactoryChain($context);
		$presenterFactoryChain->addPresenterLoader(new Kdyby\Application\PresenterLoaders\AppPresenterLoader);
		$presenterFactoryChain->addPresenterLoader(new Kdyby\Application\PresenterLoaders\AdminPresenterLoader);

		return $presenterFactoryChain;
	}



	/**
	 * @return Nette\Application\Application
	 */
	public static function createApplication(array $options = NULL)
	{
		$options['class'] = "Kdyby\Application\Kdyby"; // yes hardcode!

		$application = parent::createApplication($options);
		$context = $application->getContext();

		$context->addService('Nette\\Application\\IRouter', array(__CLASS__, 'createRoutes'));
		
		return $application;
	}



	public static function createRoutes()
	{
		$router = new Nette\Application\MultiRouter;
		$router[] = new Kdyby\Application\AdminRouter;

		return $router;
	}



	public function loadConfig($file)
	{
		$name = Environment::getName();

		$kdybyConfigFile = Nette\Environment::expand(self::$kdybyConfigFile);
		$appConfigFile = Nette\Environment::expand($file ?: $this->defaultConfigFile);

		$kdybyConfig = Nette\Config\Config::fromFile($kdybyConfigFile, $name);
		$appConfig = Nette\Config\Config::fromFile($appConfigFile, $name);

		$mergedConfig = array_replace_recursive($kdybyConfig->toArray(), $appConfig->toArray());
		$config = new Nette\Config\Config($mergedConfig);

		return parent::loadConfig($config);
	}



//	/**
//	 * @return Kdyby\Application\DatabaseManager
//	 */
//	public static function createDatabaseManager(array $options = NULL)
//	{
//		$dm = new Kdyby\Application\DatabaseManager;
//		$environmentName = Environment::getName();
//
//		$config = Environment::getConfig();
//		$context = Environment::getApplication()->getContext();
//		$em = $context->getService('Doctrine\ORM\EntityManager');
//		$dm->setEntityManager($em);
//		// Doctrine\DBAL\Types\Type::addType('set', 'Kdyby\Doctrine\Type\SetType');
//
//		$services = array();
//		$servicesFile = Environment::expand(isset($options['servicesFile']) ? $options['servicesFile'] : self::$defaultServicesFile);
//
//		// parse and load special services
//		if (file_exists($servicesFile)) {
//			$services = Nette\Config\Config::fromFile($servicesFile);
//		}
//
//		foreach ($services as $key => $value) {
//			$serviceName = strtr($key, '-', '\\'); // limited INI chars
//			$definition = is_string($value) ? array('class' => $value) : (array)$value;
//
//			$singleton = isset($value->singleton) ? (bool)$value->singleton : TRUE;
//			$options = array(
//				'definition' => $definition,
//				'context' => $context,
//				'config' => $config,
//				);
//
//			$dm->addService($serviceName, __CLASS__.'::serviceFactory', $singleton, $options);
//		}
//
//		return $dm;
//	}



	/**
	 * Get initial instance of context.
	 * @param array $services
	 * @return Kdyby\Injection\IServiceContainer
	 */
	public function createContext(array $services = array())
	{
		$loader = new Kdyby\Injection\ServiceLoader(new Kdyby\Injection\ServiceContainer());

		foreach ($services as $name => $configuration) {
			$loader->addService($name, $configuration);
		}

		foreach ($this->defaultServices as $name => $service) {
			$loader->getContainer()->addService($name, $service);
		}

		return $loader->getContainer();
	}



	/**
	 * @param Nette\Web\Session $session
	 */
	protected function setupSession(Nette\Web\Session $session)
	{
		// setup session
		if (!$session->isStarted()) {
			if (!Environment::isConsole()){
				$domain = Kdyby\Web\HttpHelpers::getDomain()->domain;
				$session->setCookieParams('/', '.'.$domain);
			}
			$session->setExpiration(Nette\Tools::YEAR);
			if (!$session->exists()) {
				$session->start();
			}
		}
	}



	/**
	 * @return Nette\Web\IUser
	 */
	public static function createIUser()
	{
		return $user = new Kdyby\Security\User;
	}



	/**
	 * @return Kdyby\ConfigHooks
	 */
	public static function createConfigHooks()
	{
		$hooks = self::$configHooks;

		$kdybyConfig = Environment::getConfig('Kdyby');
		if (isset($kdybyConfig['Core'])) {
			$hooks = $kdybyConfig['Core']->toArray() + $hooks;
		}

		return $configHooks = new Kdyby\ConfigHooks($hooks);
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



	/**
	 * @return Symfony\Component\HttpFoundation\UniversalClassLoader
	 */
	public static function createSymfony2Loader()
	{
		require_once LIBS_DIR . '/Symfony/Component/HttpFoundation/UniversalClassLoader.php';

		$loader = new \Symfony\Component\HttpFoundation\UniversalClassLoader();
		$loader->registerNamespaces(array(
			'Symfony' => LIBS_DIR,
		));
		$loader->register();

		return $loader;
	}

}