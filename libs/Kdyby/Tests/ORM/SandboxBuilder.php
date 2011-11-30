<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\ORM;

use Doctrine;
use Doctrine\Common\EventManager;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use Doctrine\DBAL\Event\Listeners\MysqlSessionInit;
use Kdyby;
use Kdyby\Doctrine\Annotations\CachedReader;
use Kdyby\Doctrine\Type;
use Nette;
use Nette\Utils\Arrays;
use Doctrine\Common\Cache\AbstractCache;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SandboxBuilder extends Nette\Object implements ISandboxBuilder
{

	/** @var array */
	public $params = array(
		'host' => 'localhost',
		'charset' => 'utf8',
		'driver' => 'pdo_mysql',
		'entityDirs' => array('%appDir%', '%kdybyFrameworkDir%'),
		'proxiesDir' => '%tempDir%/proxies',
		'proxyNamespace' => 'Kdyby\Domain\Proxies',
		'listeners' => array()
	);

	/** @var array */
	public $types = array(
		Type::CALLBACK => 'Kdyby\Doctrine\Types\Callback',
		Type::PASSWORD => 'Kdyby\Doctrine\Types\Password'
	);

	/** @var AbstractCache */
	public $cache;

	/** @var boolean */
	public $productionMode = TRUE;

	/** @var AnnotationReader */
	private $annotationReader;

	/** @var Doctrine\DBAL\Connection */
	private $dbalConnection;

	/** @var Doctrine\ORM\Configuration */
	private $ormConfiguration;

	/** @var EventManager */
	private $eventManager;

	/** @var Sandbox */
	private $sandbox;



	/**
	 * @param AbstractCache $cache
	 */
	public function __construct(AbstractCache $cache)
	{
		$this->cache = $cache;
		if (defined('KDYBY_CMS_DIR')) {
			$this->params['entityDirs'][] = '%kdybyCmsDir%';
		}

		$this->registerTypes();
		$this->registerAnnotationClasses();
	}



	/**
	 */
	public function registerTypes()
	{
		foreach ($this->types as $name => $className) {
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

		AnnotationRegistry::registerFile(KDYBY_FRAMEWORK_DIR . '/Doctrine/Mapping/Driver/DoctrineAnnotations.php');
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
	 * @return AbstractCache
	 */
	protected function getCache()
	{
		return $this->cache;
	}



	/**
	 * @return Sandbox
	 */
	public function build()
	{
		$this->createSandbox();

		$this->sandbox->params = $this->params;
		$this->buildAnnotationReader();
		$this->buildConfiguration();
		$this->buildEventManager();
		$this->buildConnection();

		return $this->sandbox;
	}



	/**
	 * @return Sandbox
	 */
	protected function createSandbox()
	{
		$this->sandbox = new Sandbox();
	}



	/**
	 * @return Sandbox
	 */
	public function getSandbox()
	{
		return $this->sandbox;
	}



	/**
	 * Registers service annotationReader
	 */
	protected function buildAnnotationReader()
	{
		if ($this->annotationReader === NULL) {
			$this->annotationReader = $this->createAnnotationReader();
		}

		$this->sandbox->addService('annotationReader', $this->annotationReader);
	}



	/**
	 * @return AnnotationReader
	 */
	protected function createAnnotationReader()
	{
		$reader = new AnnotationReader();
		$reader->setDefaultAnnotationNamespace('Doctrine\ORM\Mapping\\');
		// $reader->setAnnotationNamespaceAlias('Kdyby\Doctrine\Mapping\\', 'Kdyby');

		$reader->setIgnoreNotImportedAnnotations(TRUE);
		$reader->setEnableParsePhpImports(FALSE);

		return new CachedReader(new IndexedReader($reader), $this->getCache());
	}



	/**
	 * Registers service configuration
	 */
	protected function buildConfiguration()
	{
		if ($this->ormConfiguration === NULL) {
			$this->ormConfiguration = $this->createOrmConfiguration();
		}

		$this->sandbox->addService('configuration', $this->ormConfiguration);
	}



	/**
	 * @return Doctrine\ORM\Configuration
	 */
	protected function createOrmConfiguration()
	{
		$config = new Doctrine\ORM\Configuration;

		// Cache
		$config->setMetadataCacheImpl($this->getCache());
		$config->setQueryCacheImpl($this->getCache());

		// Metadata
		$config->setClassMetadataFactoryName('Kdyby\Doctrine\Mapping\ClassMetadataFactory');

		// Proxies
		$config->setProxyDir($this->params['proxiesDir']);
		$config->setProxyNamespace($this->params['proxyNamespace']);
		$config->setAutoGenerateProxyClasses(!$this->productionMode);
		$config->setMetadataDriverImpl($this->sandbox->annotationDriver);

		// Logger
		$config->setSQLLogger($this->createLogger());
		return $config;
	}



	/**
	 * @return Kdyby\Doctrine\Diagnostics\Panel
	 */
	protected function createLogger()
	{
		return new Kdyby\Doctrine\Diagnostics\Panel();
	}



	/**
	 * Registers service eventManager
	 */
	protected function buildEventManager()
	{
		if ($this->eventManager === NULL) {
			$this->eventManager = $this->createEventManager();
		}

		$this->sandbox->addService('eventManager', $this->eventManager);
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

			$evm->addEventSubscriber($this->sandbox->getService($listener));
		}

		$evm->addEventSubscriber(new Kdyby\Doctrine\Mapping\DiscriminatorMapDiscoveryListener(
				$this->sandbox->annotationReader,
				$this->sandbox->annotationDriver
			));
		$evm->addEventSubscriber(new Kdyby\Doctrine\Mapping\EntityDefaultsListener());
		// $evm->addEventSubscriber(new Kdyby\Media\Listeners\Mediable($this->context));

		return $evm;
	}



	/**
	 * Registers service connection
	 */
	protected function buildConnection()
	{
		if ($this->dbalConnection === NULL) {
			$this->dbalConnection = $this->createDbalConnection();
		}

		$this->sandbox->addService('connection', $this->dbalConnection);
	}



	/**
	 * @return Doctrine\DBAL\Connection
	 */
	protected function createDbalConnection()
	{
		return Doctrine\DBAL\DriverManager::getConnection(
				$this->sandbox->params,
				$this->sandbox->configuration,
				$this->sandbox->eventManager
			);
	}

}