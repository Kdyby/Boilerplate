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


namespace Kdyby;

use Nette;
use Nette\Environment;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Configurator extends Nette\Configurator
{

	/** @var string */
	private static $defaultServicesFile = "%appDir%/services.neon";

	/** @var array */
	private static $configHooks = array(
		"Nette-Security-IIdentity" => "Kdyby\\Identity"
	);



	public function __construct()
	{
		Nette\Config\Config::registerExtension('neon', 'Kdyby\Config\ConfigAdapterNeon');
		Kdyby\Template\KdybyMacros::register();
	}



	/**
	 * @return Kdyby\Application\DatabaseManager
	 */
	public static function createDatabaseManager(array $options = NULL)
	{
		$dm = new Kdyby\Application\DatabaseManager;
		$environmentName = Environment::getName();

		$config = Environment::getConfig();
		$context = Environment::getApplication()->getContext();
		$em = $context->getService('Doctrine\ORM\EntityManager');
		$dm->setEntityManager($em);
		// Doctrine\DBAL\Types\Type::addType('set', 'Kdyby\Doctrine\Type\SetType');

		$services = array();
		$servicesFile = Environment::expand(isset($options['servicesFile']) ? $options['servicesFile'] : self::$defaultServicesFile);

		// parse and load special services
		if (file_exists($servicesFile)) {
			$services = Nette\Config\Config::fromFile($servicesFile);
		}

		foreach ($services as $key => $value) {
			$serviceName = strtr($key, '-', '\\'); // limited INI chars
			$definition = is_string($value) ? array('class' => $value) : (array)$value;

			$singleton = isset($value->singleton) ? (bool)$value->singleton : TRUE;
			$options = array(
				'definition' => $definition,
				'context' => $context,
				'config' => $config,
				);

			$dm->addService($serviceName, __CLASS__.'::serviceFactory', $singleton, $options);
		}

		return $dm;
	}



	/**
	 * @author http://github.com/janmarek
	 *
	 * @param <type> $options
	 * @return Service
	 */
	public static function serviceFactory($options)
	{
		$definition = $options['definition'];
		$context = $options['context'];
		$config = $options['config'];
		$arguments = array();

		if (isset($definition["arguments"])) {
			$arguments = array_map(function ($arg) use ($context, $config) {
				if (!is_string($arg)) { // what else could it be?
					return $arg;
				}

				// %service
				// %service%service%service%se...
				if (substr($arg, 0, 1) === '%') {
					$service = $context;
					$arg = strtr($arg, '-', '\\'); // limited INI chars

					do {
						$service = $context->getService(substr($arg, 1, (stripos($arg, '%', 1) ?: strlen($arg))-1));
						$arg = substr($arg, stripos($arg, '%', 1) ?: strlen($arg));
					} while (substr($arg, 0, 1) === '%');

					return $service;
				}

				// C$variable
				if (substr($arg, 0, 2) === 'C$') {
					return $config[substr($arg, 2)];
				}

				// E$variable
				if (substr($arg, 0, 2) === 'E$') {
					return \Nette\Environment::getVariable(substr($arg, 2));
				}

				return $arg;
			}, $definition["arguments"]);
		}

		// class definition, without special initialization
		if (isset($definition["class"])) {
			if ($arguments) {
				$ref = new \ReflectionClass($definition["class"]);
			}

			$object = $arguments ? $ref->newInstanceArgs($arguments) : new $definition["class"];
		}

		// objet factory
		if (isset($definition["factory"])) {
			$object = call_user_func_array($definition["factory"], $arguments);
		}

		// inject database manager
		if ($object instanceof Kdyby\Doctrine\Service) {
			$object->setDatabaseManager($context->getService('Kdyby\Application\DatabaseManager'));
		}

		return $object;
	}



	/**
	 * @param Nette\Web\Session $session
	 */
	public static function setupSession(Nette\Web\Session $session)
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
		$dir = FileSystem::prepareWritableDir('%varDir%/cache');

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
			$dir = FileSystem::prepareWritableDir('%tempDir%/memcache');
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



	/**
	 * @return Zend\Loader\StandardAutoloader
	 */
	public static function createZendFramework2Loader()
	{
		require_once LIBS_DIR . '/Zend/Loader/StandardAutoloader.php';

		return $zendLoader = new \Zend\Loader\StandardAutoloader();
	}

}