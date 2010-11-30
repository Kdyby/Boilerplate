<?php

/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Kdyby\Doctrine;

use Nette\Environment;

/**
 * Factories for doctrine
 *
 * @author	Patrik Votoček
 * @package	Nella\Doctrine
 */
class Factory extends \Nette\Object
{
	/**
	 * @throws InvalidStateException
	 */
	final public function __construct()
	{
		throw new \InvalidStateException("Cannot instantiate static class " . get_called_class());
	}

	/**
	 * @return Cache
	 */
	protected  static function createCache()
	{
		return new Cache(Environment::getCache('Doctrine'));
	}

	/**
	 * @return Doctrine\Common\EventManager
	 */
	protected static function createEventManager()
	{
		return new \Doctrine\Common\EventManager;
	}

	/**
	 * @return Nella\Doctrine\Panel
	 */
	protected static function createLogger($serviceName = 'Doctrine\ORM\EntityManager')
	{
		return \Nella\Doctrine\Panel::createAndRegister($serviceName);
	}

	/**
	 * @param string
	 * @param string|bool
	 * @return Doctrine\DBAL\Event\Listeners\MysqlSessionInit
	 */
	protected static function createMysqlSessionListener($charset = 'utf8', $collation = FALSE)
	{
		return new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit($charset, $collation);

	}

	/**
	 * @return Doctrine\ORM\Configuration
	 */
	protected static function createConfiguration(array $database, $serviceName = 'Doctrine\ORM\EntityManager')
	{
		$config = new \Doctrine\ORM\Configuration;

		// Cache
		$cache = static::createCache();
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);

		// Metadata
		$config->setMetadataDriverImpl($config->newDefaultAnnotationDriver(array(APP_DIR)));

		// Proxies
		$config->setProxyDir(Environment::getVariable('proxyDir', APP_DIR . "/proxies"));
		$config->setProxyNamespace('App\Models\Proxies');
		if (Environment::isProduction()) {
			$config->setAutoGenerateProxyClasses(FALSE);
		} else {
			$config->setAutoGenerateProxyClasses(TRUE);
		}

		// Profiler
		if (isset($database['profiler']) && $database['profiler']) {
			$config->setSQLLogger(static::createLogger($serviceName));
		}

		return $config;
	}

	/**
	 * @param string
	 * @return Doctrine\ORM\EntityManager
	 */
	public static function createEntityManager()
	{
		$context = Environment::getApplication()->context;
		$serviceName = 'Doctrine\ORM\EntityManager';
		$database = (array) Environment::getConfig('database');

		// Load config
		$config = self::createConfiguration($database, $serviceName);

		$event = static::createEventManager();
		// Special event for MySQL
		if (isset($database['driver']) && $database['driver'] == "pdo_mysql" && isset($database['charset'])) {
			$event->addEventSubscriber(self::createMysqlSessionListener(
				$database['charset'],
				isset($database['collation']) ? $database['collation'] : FALSE
			));
		}

		// Entity manager
		return \Doctrine\ORM\EntityManager::create($database, $config, $event);
	}
}