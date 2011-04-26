<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\DBAL\Types\Type as DoctrineTypes;
use Nette;
use Kdyby;



/**
 * Factories for doctrine
 *
 * @author	Patrik Votoček
 * @package	Nella\Doctrine
 */
final class ServiceFactory extends Nette\Object
{

	/**
	 * @throws InvalidStateException
	 */
	final public function __construct()
	{
		throw new Nette\InvalidStateException("Cannot instantiate static class " . get_called_class());
	}



	/**
	 * @return void
	 */
	public static function registerTypes()
	{
		DoctrineTypes::addType('callback', '\Kdyby\Doctrine\Types\CallbackType');
	}



	/**
	 * Add a new default annotation driver with a correctly configured annotation reader.
	 *
	 * @param array $paths
	 * @return Mapping\Driver\AnnotationDriver
	 */
	public static function newDefaultAnnotationDriver($paths = array())
	{
		$reader = new Doctrine\Common\Annotations\AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
		// $reader->setAnnotationNamespaceAlias('Kdyby\Doctrine\Mapping\\', 'Kdyby');

		return new Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, (array)$paths);
	}



	/**
	 * @param array $dirs
	 * @return Doctrine\ORM\Configuration
	 */
	public static function createConfiguration(array $dirs = array())
	{
		$config = new Doctrine\ORM\Configuration;

		// Cache
		$cache = new Doctrine\Common\Cache\ArrayCache();
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);

		// Metadata
		$dirs = $dirs ?: array(APP_DIR, KDYBY_DIR);
		$config->setMetadataDriverImpl(self::newDefaultAnnotationDriver($dirs));

		// Proxies
		$config->setProxyDir(TEMP_DIR . "/proxies");
		$config->setProxyNamespace('Kdyby\Models\Proxies');
		$config->setAutoGenerateProxyClasses(Nette\Environment::isProduction() === FALSE);

		return $config;
	}



	/**
	 * @param array $database
	 * @param Doctrine\ORM\Configuration $configuration
	 * @param Doctrine\Common\EventManager $event
	 * @return Doctrine\ORM\EntityManager
	 */
	public static function createEntityManager(array $database, Doctrine\ORM\Configuration $configuration = NULL, Doctrine\Common\EventManager $event = NULL)
	{
		// Entity manager
		$configuration = $configuration ?: self::createConfiguration();
		if (key_exists('driver', $database) && $database['driver'] == "pdo_mysql" && key_exists('charset', $database)) {
			if (!$event) {
				$event = new Doctrine\Common\EventManager;
			}
			$event->addEventSubscriber(new Doctrine\DBAL\Event\Listeners\MysqlSessionInit($database['charset']));
		}

		// Profiler
		if (isset($database['profiler']) && $database['profiler']) {
			$configuration->setSQLLogger(Panel::create());
		}

		return Doctrine\ORM\EntityManager::create($database, $configuration, $event);
	}

}
