<?php

namespace Kdyby;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Configurator extends Nette\Object
{


	/**
	 * @return Nette\Web\IUser
	 */
	public static function createIUser()
	{
//		$dtm = Nette\Environment::getService("Kdyby\\Database\\DtM");

		return $user = new Kdyby\Security\User;
	}



	/**
	 * @return Nette\Caching\FileJournal
	 */
	public static function createCacheStorage()
	{
		$context = new Nette\Context;
		$context->addService('Nette\\Caching\\ICacheJournal', callback('Nette\Configurator::createCacheJournal'));

		$dir = FileSystem::prepareWritableDir(Nette\Environment::expand('%tempDir%/cache'));

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
			$dir = FileSystem::prepareWritableDir(Nette\Environment::expand('%tempDir%/memcache'));
			return new Nette\Caching\FileJournal($dir);
		}
	}

}