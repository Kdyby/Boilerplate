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
use Doctrine\Common\EventManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;
use Kdyby;
use Kdyby\Doctrine\Annotations\CachedReader;
use Nette;
use Nette\Utils\Arrays;



/**
 * @author Filip Procházka
 */
class ContainerBuilder extends Nette\Object implements Kdyby\Doctrine\IContainerBuilder
{

	/** @var array */
	private $params = array(
		'host' => 'localhost',
		'charset' => 'utf8',
		'driver' => 'pdo_mysql',
		'entityDirs' => array('%appDir%', '%kdybyFrameworkDir%'),
		'proxiesDir' => '%tempDir%/proxies',
		'proxyNamespace' => 'Kdyby\Domain\Proxies',
		'listeners' => array()
	);

	/** @var array */
	private static $types = array(
		'callback' => '\Kdyby\Doctrine\ORM\Types\Callback',
		'password' => '\Kdyby\Doctrine\ORM\Types\Password'
	);

	/** @var Kdyby\Doctrine\Cache */
	private $cache;

	/** @var Doctrine\Common\Cache\Cache */
	private $metadataCache;

	/** @var Doctrine\Common\Cache\Cache */
	private $queryCache;

	/** @var Doctrine\Common\Cache\Cache */
	private $annotationCache;

	/** @var boolean */
	private $productionMode = TRUE;

	/** @var Doctrine\DBAL\Connection */
	private $dbalConnection;

	/** @var Doctrine\ORM\Configuration */
	private $ormConfiguration;

	/** @var AnnotationReader */
	private $annotationReader;

	/** @var EventManager */
	private $eventManager;

	/** @var Container */
	protected $container;



	/**
	 * @param Kdyby\Doctrine\Cache $cache
	 * @param array $parameters
	 */
	public function __construct(Kdyby\Doctrine\Cache $cache, $parameters = array())
	{
		$this->cache = $cache;
		$this->params = Arrays::mergeTree($parameters, $this->params);

		if (defined('KDYBY_CMS_DIR')) {
			$this->params['entityDirs'][] = '%kdybyCmsDir%';
		}
	}



	/**
	 * @param Doctrine\Common\Cache\Cache $cache
	 */
	public function setMetadataCache(Doctrine\Common\Cache\Cache $cache)
	{
		$this->metadataCache = $cache;
	}



	/**
	 * @return Doctrine\Common\Cache\Cache
	 */
	final protected function getMetadataCache()
	{
		if ($this->metadataCache === NULL) {
			return $this->cache;
		}

		return $this->metadataCache;
	}



	/**
	 * @param Doctrine\Common\Cache\Cache $cache
	 */
	public function setQueryCache(Doctrine\Common\Cache\Cache $cache)
	{
		$this->queryCache = $cache;
	}



	/**
	 * @return Doctrine\Common\Cache\Cache
	 */
	final protected function getQueryCache()
	{
		if ($this->queryCache === NULL) {
			return $this->cache;
		}

		return $this->queryCache;
	}



	/**
	 * @param Doctrine\Common\Cache\Cache $cache
	 */
	public function setAnnotationCache(Doctrine\Common\Cache\Cache $cache)
	{
		$this->annotationCache = $cache;
	}



	/**
	 * @return Doctrine\Common\Cache\Cache
	 */
	final protected function getAnnotationCache()
	{
		if ($this->annotationCache === NULL) {
			return $this->cache;
		}

		return $this->annotationCache;
	}



	/**
	 * @param boolean $mode
	 */
	public function setProductionMode($mode = TRUE)
	{
		$this->productionMode = (bool)$mode;
	}



	/**
	 */
	public function registerTypes()
	{
		foreach (self::$types as $name => $className) {
			if (!Type::hasType($name)) {
				Type::addType($name, $className);
			}
		}
	}



	/**
	 */
	public function registerAnnotationClasses()
	{
		$loader = Kdyby\Loaders\SplClassLoader::getInstance();
		foreach ($loader->getTypeDirs('Doctrine\ORM') as $dir) {
			AnnotationRegistry::registerFile($dir . '/Mapping/Driver/DoctrineAnnotations.php');
		}

		AnnotationRegistry::registerFile(KDYBY_FRAMEWORK_DIR . '/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
	}



	/**
	 * @param Nette\DI\Container $container
	 */
	public function expandParams(Nette\DI\Container $container)
	{
		array_walk_recursive($this->params, function (&$value, $key) use ($container) {
			$value = $container->expand($value);
		});
	}



	/**
	 * @return Doctrine\DBAL\Connection
	 */
	protected function createDbalConnection()
	{
		return Doctrine\DBAL\DriverManager::getConnection(
				$this->params,
				$this->getOrmConfiguration(),
				$this->getEventManager()
			);
	}



	/**
	 * @return Doctrine\DBAL\Connection
	 */
	final public function getDbalConnection()
	{
		if ($this->dbalConnection === NULL) {
			$this->dbalConnection = $this->createDbalConnection();
		}

		return $this->dbalConnection;
	}



	/**
	 * @return Diagnostics\Panel
	 */
	protected function createLogger()
	{
		return Diagnostics\Panel::register();
	}



	/**
	 * @return Doctrine\ORM\Configuration
	 */
	protected function createOrmConfiguration()
	{
		$config = new Doctrine\ORM\Configuration;

		// Cache
		$config->setMetadataCacheImpl($this->getMetadataCache());
		$config->setQueryCacheImpl($this->getQueryCache());

		// Metadata
		$config->setClassMetadataFactoryName('Kdyby\Doctrine\ORM\Mapping\ClassMetadataFactory');

		// Proxies
		$config->setProxyDir($this->params['proxiesDir']);
		$config->setProxyNamespace($this->params['proxyNamespace']);
		$config->setAutoGenerateProxyClasses(!$this->productionMode);
		if (!$this->productionMode) {
			$config->setSQLLogger($this->createLogger());
		}

		return $config;
	}



	/**
	 * @return Doctrine\ORM\Configuration
	 */
	final public function getOrmConfiguration()
	{
		if ($this->ormConfiguration === NULL) {
			$this->ormConfiguration = $this->createOrmConfiguration();
		}

		return $this->ormConfiguration;
	}



	/**
	 * @return AnnotationReader
	 */
	protected function createAnnotationReader()
	{
		$reader = new AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
		// $reader->setAnnotationNamespaceAlias('Kdyby\Doctrine\ORM\Mapping\\', 'Kdyby');

		$reader->setIgnoreNotImportedAnnotations(TRUE);
		$reader->setEnableParsePhpImports(FALSE);

		return new CachedReader(new IndexedReader($reader), $this->getAnnotationCache());
	}



	/**
	 * @return AnnotationReader
	 */
	final public function getAnnotationReader()
	{
		if ($this->annotationReader === NULL) {
			$this->annotationReader = $this->createAnnotationReader();
		}

		return $this->annotationReader;
	}



	/**
	 * @return EventManager
	 */
	protected function createEventManager()
	{
		$evm = new EventManager;

		if (key_exists('driver', $this->params) && $this->params['driver'] == "pdo_mysql" && key_exists('charset', $this->params)) {
			$evm->addEventSubscriber(new MysqlSessionInit($this->params['charset']));
		}

		foreach ($this->params['listeners'] as $listener) {
			if (class_exists($listener)) {
				$evm->addEventSubscriber(new $listener);
				continue;
			}

			$evm->addEventSubscriber($this->container->getService($listener));
		}

		$evm->addEventSubscriber($this->container->discriminatorMapDiscoveryListener);
		$evm->addEventSubscriber($this->container->entityDefaultsListener);
		// $evm->addEventSubscriber(new Kdyby\Media\Listeners\Mediable($this->context));

		return $evm;
	}



	/**
	 * @return EventManager
	 */
	final public function getEventManager()
	{
		if ($this->eventManager === NULL) {
			$this->eventManager = $this->createEventManager();
		}

		return $this->eventManager;
	}



	/**
	 * @return Container
	 */
	public function build()
	{
		$this->createContainer();
		$this->buildAnnotationReader();
		$this->buildConfiguration();
		$this->buildEventManager();
		$this->buildConnection();

		return $this->getContainer();
	}



	/**
	 * @return Container
	 */
	protected function createContainer()
	{
		$this->container = new Container();
	}



	/**
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
	}



	/**
	 * Registers service annotationReader
	 */
	protected function buildAnnotationReader()
	{
		$this->container->addService('annotationReader', $this->getAnnotationReader());
	}



	/**
	 * Registers service configuration
	 */
	protected function buildConfiguration()
	{
		$this->container->params = $this->params;

		$config = $this->getOrmConfiguration();
		$config->setMetadataDriverImpl($this->container->annotationDriver);
		$this->container->addService('configuration', $config);
	}



	/**
	 * Registers service eventManager
	 */
	protected function buildEventManager()
	{
		$this->container->addService('eventManager', $this->getEventManager());
	}



	/**
	 * Registers service connection
	 */
	protected function buildConnection()
	{
		$this->container->addService('connection', $this->getDbalConnection());
	}

}