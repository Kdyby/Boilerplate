<?php

namespace Kdyby;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Configurator extends Nette\Object
{

	/** @var array */
	private static $configHooks = array(
		"Nette-Security-IIdentity" => "Kdyby\\Identity"
	);



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

		$kdybyConfig = Nette\Environment::getConfig('kdyby');
		if (isset($kdybyConfig['core'])) {
			$hooks = $kdybyConfig['core']->toArray() + $hooks;
		}

		return $configHooks = new Kdyby\ConfigHooks($hooks);
	}



	/**
	 * @return Nette\Caching\FileJournal
	 */
	public static function createCacheStorage()
	{
		$context = new Nette\Context;
		$context->addService('Nette\\Caching\\ICacheJournal', callback('Nette\Configurator::createCacheJournal'));

		$dir = FileSystem::prepareWritableDir('%tempDir%/cache');

		return new Kdyby\FileStorage($dir, $context);
	}



	/**
	 * @return Nette\Caching\MemcachedStorage
	 */
	public static function createMemcacheStorage($options)
	{
		$context = new Nette\Context;
		$context->addService('Nette\\Caching\\ICacheJournal', callback('Kdyby\Configurator::createMemcacheJournal'));

		$config = Nette\Environment::getConfig('memcache');
		return new \Nette\Caching\MemcachedStorage($config['host'], $config['port'], $options['prefix'], $context);
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
	 * @return \Symfony\Component\HttpFoundation\UniversalClassLoader
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