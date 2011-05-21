<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\DBAL\Types\Type as DoctrineTypes;
use Kdyby;
use Nette;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Nette\DI\Container $container
 * @property-read Cache $cache
 * @property-read Panel $logger
 * @property-read Doctrine\ORM\Configuration $configurator
 * @property-read Doctrine\ORM\Mapping\Driver\AnnotationDriver $annotationDriver
 * @property-read Doctrine\DBAL\Event\Listeners\MysqlSessionInit $mysqlSessionInitListener
 * @property-read Doctrine\Common\EventManager $eventManager
 * @property-read Doctrine\ORM\EntityManager $entityManager
 */
class Container extends Kdyby\DI\Container
{

	/** @var array */
	private static $types = array(
		'callback' => '\Kdyby\Doctrine\Types\Callback'
	);



	/**
	 * Registers doctrine types
	 */
	public function __construct()
	{
		foreach (self::$types as $name => $className) {
			if (!DoctrineTypes::hasType($name)) {
				DoctrineTypes::addType($name, $className);
			}
		}
	}



	/**
	 * @return Cache
	 */
	protected function createServiceCache()
	{
		return new Cache($this->container->cacheStorage);
	}



	/**
	 * @return Panel
	 */
	protected function createServiceLogger()
	{
		return Panel::register();
	}



	/**
	 * @return Doctrine\ORM\Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		$reader = new Doctrine\Common\Annotations\AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
		// $reader->setAnnotationNamespaceAlias('Kdyby\Doctrine\Mapping\\', 'Kdyby');

		$dirs = $this->getParam('entityDirs', $this->container->getParam('entityDirs', array(APP_DIR, KDYBY_DIR)));
		return new Kdyby\Doctrine\Mapping\Driver\AnnotationDriver($reader, (array)$dirs);
	}



	/**
	 * @return Doctrine\ORM\Configuration
	 */
	protected function createServiceConfiguration()
	{
		$config = new Doctrine\ORM\Configuration;

		// Cache
		$config->setMetadataCacheImpl($this->hasService('metadataCache') ? $this->metadataCache : $this->cache);
		$config->setQueryCacheImpl($this->hasService('queryCache') ? $this->queryCache : $this->cache);

		// Metadata
		$config->setMetadataDriverImpl($this->annotationDriver);

		// Proxies
		$config->setProxyDir($this->getParam('proxyDir', $this->container->expand("%appDir%/proxies")));
		$config->setProxyNamespace($this->getParam('proxyNamespace', 'Kdyby\Models\Proxies'));
		if ($this->container->getParam('productionMode')) {
			$config->setAutoGenerateProxyClasses(FALSE);

		} else {
			$config->setAutoGenerateProxyClasses(TRUE);
			$config->setSQLLogger($this->logger);
		}

		return $config;
	}



	/**
	 * @return Doctrine\DBAL\Event\Listeners\MysqlSessionInit
	 */
	protected function createServiceMysqlSessionInitListener()
	{
		$database = $this->container->getParam('database', array());
		return new Doctrine\DBAL\Event\Listeners\MysqlSessionInit($database['charset']);
	}



	/**
	 * @return Doctrine\Common\EventManager
	 */
	protected function createServiceEventManager()
	{
		$evm = new Doctrine\Common\EventManager;
		foreach ($this->getParam('listeners', array()) as $listener) {
			$evm->addEventSubscriber($this->getService($listener));
		}

		return $evm;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function createServiceEntityManager()
	{
		$database = $this->container->getParam('database', array());

		if (key_exists('driver', $database) && $database['driver'] == "pdo_mysql" && key_exists('charset', $database)) {
			$this->eventManager->addEventSubscriber($this->mysqlSessionInitListener);
		}

		$this->freeze();
		return Doctrine\ORM\EntityManager::create((array)$database, $this->configuration, $this->eventManager);
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

}