<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\EventManager;
use Kdyby;
use Nette;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Kdyby\DI\Container $context
 * @property-read Cache $cache
 * @property-read Diagnostics\Panel $logger
 * @property-read Doctrine\ORM\Configuration $configurator
 * @property-read Doctrine\ORM\Mapping\Driver\AnnotationDriver $annotationDriver
 * @property-read Doctrine\DBAL\Event\Listeners\MysqlSessionInit $mysqlSessionInitListener
 * @property-read EventManager $eventManager
 * @property-read EntityManager $entityManager
 */
class Container extends Kdyby\DI\Container implements Kdyby\Doctrine\IContainer
{

	/** @var array */
	public $params = array(
			'host' => 'localhost',
			'charset' => 'utf8',
			'driver' => 'pdo_mysql',
			'entityDirs' => array('%appDir%', '%kdybyDir%'),
			'proxiesDir' => '%tempDir%/proxies',
			'proxyNamespace' => 'Kdyby\Domain\Proxies',
			'listeners' => array(),
		);

	/** @var array */
	private static $types = array(
		'callback' => '\Kdyby\Doctrine\ORM\Types\Callback',
		'password' => '\Kdyby\Doctrine\ORM\Types\Password'
	);



	/**
	 * Registers doctrine types
	 *
	 * @param Kdyby\DI\Container $context
	 * @param array $parameters
	 */
	public function __construct(Kdyby\DI\Container $context, $parameters = array())
	{
		$this->addService('context', $context);
		$this->params += (array)$parameters;

		array_walk_recursive($this->params, function (&$value) use ($context) {
			$value = $context->expand($value);
		});

		foreach (self::$types as $name => $className) {
			if (!Type::hasType($name)) {
				Type::addType($name, $className);
			}
		}
	}



	/**
	 * @return Cache
	 */
	protected function createServiceCache()
	{
		return new Cache($this->context->cacheStorage);
	}



	/**
	 * @return Diagnostics\Panel
	 */
	protected function createServiceLogger()
	{
		return Diagnostics\Panel::register();
	}



	/**
	 * @return Doctrine\ORM\Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		$reader = new Doctrine\Common\Annotations\AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
		// $reader->setAnnotationNamespaceAlias('Kdyby\Doctrine\ORM\Mapping\\', 'Kdyby');

		$reader->setIgnoreNotImportedAnnotations(TRUE);
		$reader->setEnableParsePhpImports(FALSE);

		$reader = new Doctrine\Common\Annotations\CachedReader(
			new Doctrine\Common\Annotations\IndexedReader($reader),
			new Doctrine\Common\Cache\ArrayCache()
		);

		return new Kdyby\Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, $this->params['entityDirs']);
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
		$config->setClassMetadataFactoryName('Kdyby\Doctrine\ORM\Mapping\ClassMetadataFactory');
		$config->setMetadataDriverImpl($this->annotationDriver);

		// Proxies
		$config->setProxyDir($this->params['proxiesDir']);
		$config->setProxyNamespace($this->getParam('proxyNamespace', 'Kdyby\Domain\Proxies'));
		if ($this->context->params['productionMode']) {
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
		return new Doctrine\DBAL\Event\Listeners\MysqlSessionInit($this->params['charset']);
	}



	/**
	 * @return EventManager
	 */
	protected function createServiceEventManager()
	{
		$evm = new EventManager;
		foreach ($this->params['listeners'] as $listener) {
			$evm->addEventSubscriber($this->getService($listener));
		}

		// $evm->addEventSubscriber(new Kdyby\Media\Listeners\Mediable($this->context));
		return $evm;
	}



	/**
	 * @return EntityManager
	 */
	protected function createServiceEntityManager()
	{
		if (key_exists('driver', $this->params) && $this->params['driver'] == "pdo_mysql" && key_exists('charset', $this->params)) {
			$this->eventManager->addEventSubscriber($this->mysqlSessionInitListener);
		}

		$this->freeze();
		return EntityManager::create($this->params, $this->configuration, $this->eventManager);
	}



	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @param string $entityName
	 * @return EntityRepository
	 */
	public function getRepository($entityName)
	{
		return $this->getEntityManager()->getRepository($entityName);
	}



	/**
	 * @param string $className
	 * @return bool
	 */
	public function isManaging($className)
	{
		return $this->getEntityManager()->getMetadataFactory()->hasMetadataFor($className);
	}

}