<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Package\DoctrinePackage\DI;

use Kdyby;
use Kdyby\Packages\PackagesContainer;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;
use Nette\Reflection\ClassType;



/**
 * OrmExtension is an extension for the Doctrine ORM library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class OrmExtension extends Kdyby\Config\CompilerExtension
{

	/**
	 * @var array
	 */
	public $proxyDefaults = array(
		'autoGenerateProxyClasses' => '%kdyby.debug%',
		'proxyDir' => '%tempDir%/proxies',
		'proxyNamespace' => 'Kdyby\Domain\Proxy'
	);

	/**
	 * @var array
	 */
	public $entityManagerDefaults = array(
		'autoMapping' => TRUE,
		'connection' => NULL,
		'mappings' => array(),
		'metadataFactoryClass' => 'Kdyby\Doctrine\Mapping\ClassMetadataFactory',
		'metadataCacheDriver' => "file",
		'queryCacheDriver' => "file",
		'resultCacheDriver' => "file",
		'dql' => array(),
		'hydrators' => array(),
	);

	/**
	 * @var array
	 */
	public $mappingsDefaults = array(
		'mapping' => TRUE,
		'type' => NULL,
		'dir' => FALSE,
		'alias' => FALSE,
		'prefix' => FALSE
	);

	/**
	 * @var array
	 */
	public $memcacheDefaults = array(
		'type' => 'memcache',
		'host' => 'localhost',
		'port' => 11211,
		'instanceClass' => 'Memcache',
	);

	/**
	 * @var array
	 */
	public $dqlDefaults = array(
		'string' => array(),
		'numeric' => array(),
		'datetime' => array()
	);

	/**
	 * @var array
	 */
	public $metadataDriverClasses = array(
		'driverChain' => 'Doctrine\ORM\Mapping\Driver\DriverChain',
		'annotation' => 'Kdyby\Doctrine\Mapping\Driver\AnnotationDriver',
		'xml' => 'Doctrine\ORM\Mapping\Driver\XmlDriver',
		'yml' => 'Doctrine\ORM\Mapping\Driver\YamlDriver',
		'php' => 'Doctrine\ORM\Mapping\Driver\PHPDriver',
		'staticphp' => 'Doctrine\ORM\Mapping\Driver\StaticPHPDriver'
	);

	public $cacheDriverClasses = array(
		'file' => 'Kdyby\Doctrine\Cache',
		'array' => 'Doctrine\Common\Cache\ArrayCache',
		'apc' => 'Doctrine\Common\Cache\ApcCache',
		'xcache' => 'Doctrine\Common\Cache\XcacheCache',
		'memcache' => 'Doctrine\Common\Cache\MemcacheCache'
	);

	/**
	 * Used inside metadata driver method to simplify aggregation of data.
	 * @var array
	 */
	protected $aliasMap = array();

	/**
	 * Used inside metadata driver method to simplify aggregation of data.
	 * @var array
	 */
	protected $drivers = array();

	/**
	 * @var array
	 */
	private $entityManagers = array();

	/**
	 * @var \Kdyby\Packages\PackagesContainer|\Kdyby\Packages\Package[]
	 */
	private $packages;



	/**
	 * @param \Kdyby\Packages\PackagesContainer $packages
	 */
	public function __construct(PackagesContainer $packages)
	{
		$this->packages = $packages;
	}



	public function loadConfiguration()
	{
		$container = parent::loadConfiguration();
		$config = $this->getConfig();

		$this->entityManagers = isset($config['entityManagers']) ? $config['entityManagers'] : array('default' => $config);

		// default entity manger
		if (empty($config['defaultEntityManager'])) {
			$keys = array_keys($this->entityManagers);
			$config['defaultEntityManager'] = reset($keys);
		}
		$container->parameters['doctrine']['defaultEntityManager'] = $config['defaultEntityManager'];

		// entity managers list
		foreach (array_keys($this->entityManagers) as $name) {
			$container->parameters['doctrine']['entityManagers'][$name] = 'doctrine.orm.' . $name . 'EntityManager';
		}

		// proxy options
		foreach (self::getOptions($config, $this->proxyDefaults) as $key => $value) {
			$container->parameters['doctrine']['orm'][$key] = $value;
		}

		// load entity managers
		foreach ($this->entityManagers as $name => $entityManager) {
			$entityManager['name'] = $name;
			$this->loadOrmEntityManager($entityManager);
		}

		$this->addAlias('doctrine.orm.entityManager', 'doctrine.orm.' . $config['defaultEntityManager'] . 'EntityManager');
	}



	/**
	 * Loads a configured ORM entity manager.
	 *
	 * @param array $config A configured ORM entity manager.
	 */
	protected function loadOrmEntityManager(array $config)
	{
		$container = $this->getContainerBuilder();
		$entityManagerName = 'doctrine.orm.' . $config['name'] . 'EntityManager';

		// options
		$options = self::getOptions($config, $this->entityManagerDefaults);
		$options['name'] = $config['name'];
		$options['autoMapping'] = !isset($config['mappings']) && $options['autoMapping'];
		if (!$options['autoMapping'] && !isset($config['mappings'])) {
			throw new Kdyby\InvalidStateException($config['name'] . 'EntityManager: You have disabled "autoMapping" and no "mappings" section is defined.');
		}

		// configuration
		$configuration = $container->addDefinition($entityManagerName . '.configuration')
			->setClass('Doctrine\ORM\Configuration');

		foreach ($this->getConfigurationOptions($options) as $method => $arg) {
			$configuration->addSetup('set' . ucfirst($method), array($arg));
		}

		// hydrators
		foreach ($options['hydrators'] as $name => $class) {
			$configuration->addSetup('addCustomHydrationMode', array($name, $class));
		}

		// dql functions
		if (!empty($options['dql'])) {
			$options['dql'] = self::getOptions($options['dql'], $this->dqlDefaults);
			foreach ($options['dql']['string'] as $name => $function) {
				$configuration->addSetup('addCustomStringFunction', array($name, $function));
			}
			foreach ($options['dql']['numeric'] as $name => $function) {
				$configuration->addSetup('addCustomNumericFunction', array($name, $function));
			}
			foreach ($options['dql']['datetime'] as $name => $function) {
				$configuration->addSetup('addCustomDatetimeFunction', array($name, $function));
			}
		}

		// connection
		if (!isset($options['connection'])) {
			$options['connection'] = $container->parameters['doctrine']['defaultConnection'];
		}
		$connectionName = 'doctrine.dbal.' . $options['connection'] . 'Connection';

		// mappings
		$this->loadOrmEntityManagerMappingInformation($options);

		// cache drivers
		$this->loadOrmCacheDrivers($options);

		// entity manager
		$container->addDefinition($entityManagerName)
			->setClass('Doctrine\ORM\EntityManager')
			->setFactory('Doctrine\ORM\EntityManager::create', array(
				'@' . $connectionName,
				'@' . $entityManagerName . '.configuration'
			));

		// event manager
		$this->addAlias($entityManagerName . '.eventManager', $connectionName . '.eventManager')
			->setAutowired(FALSE);
	}



	/**
	 * @param array $config
	 * @return array
	 */
	protected function getConfigurationOptions($config)
	{
		$entityManagerName = 'doctrine.orm.' . $config['name'] . 'EntityManager';
		return array(
			'metadataCacheImpl' => '@' . $entityManagerName . '.metadataCache',
			'queryCacheImpl' => '@' . $entityManagerName . '.queryCache',
			'resultCacheImpl' => '@' . $entityManagerName . '.resultCache',
			'metadataDriverImpl' => '@' . $entityManagerName . '.metadataDriver',
			'classMetadataFactoryName' => $config['metadataFactoryClass'],
			'proxyDir' => '%doctrine.orm.proxyDir%',
			'proxyNamespace' => '%doctrine.orm.proxyNamespace%',
			'autoGenerateProxyClasses' => '%doctrine.orm.autoGenerateProxyClasses%',
		);
	}



	/**
	 * Loads an ORM entity managers package mapping information.
	 *
	 * There are two distinct configuration possibilities for mapping information:
	 *
	 * 1. Specify a package and optionally details where the entity and mapping information reside.
	 * 2. Specify an arbitrary mapping location.
	 *
	 * @example
	 *
	 *  doctrine.orm:
	 *	 mappings:
	 *		 MyPackage1: ~
	 *		 MyPackage2: yml
	 *		 MyPackage3: { type: annotation, dir: Entities/ }
	 *		 MyPackage4: { type: xml, dir: Resources/config/doctrine/mapping }
	 *		 MyPackage5:
	 *			 type: yml
	 *			 dir: [package-mappings1/, package-mappings2/]
	 *			 alias: packageAlias
	 *		 arbitrary_key:
	 *			 type: xml
	 *			 dir: %kernel.dir%/../src/vendor/DoctrineExtensions/lib/DoctrineExtensions/Entities
	 *			 prefix: DoctrineExtensions\Entities\
	 *			 alias: DExt
	 *
	 * In the case of packages everything is really optional (which leads to autodetection for this package) but
	 * in the mappings key everything except alias is a required argument.
	 *
	 * @param array $config A configured ORM entity manager.
	 */
	protected function loadOrmEntityManagerMappingInformation(array $config)
	{
		$container = $this->getContainerBuilder();

		// reset state of drivers and alias map. They are only used by this methods and children.
		$this->drivers = array();
		$this->aliasMap = array();

		$this->loadMappingInformation($config);
		$this->registerMappingDrivers($config);

		$container->getDefinition('doctrine.orm.' . $config['name'] . 'EntityManager.configuration')
			->addSetup('setEntityNamespaces', array($this->aliasMap));
	}



	/**
	 * @param array $config A configured object manager.
	 */
	protected function loadMappingInformation(array $config)
	{
		$container = $this->getContainerBuilder();
		$mappings = $config['mappings'];

		// automatically register package mappings
		if ($config['autoMapping']) {
			foreach (array_keys($container->parameters['kdyby']['packages']) as $package) {
				if (!isset($mappings[$package])) {
					$mappings[$package] = NULL;
				}
			}
		}

		foreach ($mappings as $mappingName => $mappingConfig) {
			$options = self::getOptions((array)$mappingConfig, $this->mappingsDefaults, TRUE);
			if ($mappingConfig !== NULL && $options['mapping'] === FALSE) {
				continue;
			}

			$options['name'] = $mappingName;
			$options['dir'] = $container->expand($options['dir']);
			if (empty($options['alias'])) {
				$options['alias'] = substr($mappingName, 0, -7);
			}

			// a package configuration is detected by realizing that the specified dir is not absolute and existing
			$options['package'] = !file_exists($options['dir']);

			$this->loadPackageMappingInformation($options, $config);
		}
	}



	/**
	 * Register all the collected mapping information with the object manager by registering the appropriate mapping drivers.
	 *
	 * @param array $config
	 */
	protected function registerMappingDrivers($config)
	{
		$container = $this->getContainerBuilder();
		$entityManagerName = 'doctrine.orm.' . $config['name'] . 'EntityManager';

		// configure metadata driver for each package based on the type of mapping files found
		if (!$container->hasDefinition($entityManagerName . '.metadataDriver')) {
			$container->addDefinition($entityManagerName . '.metadataDriver')
				->setClass('Doctrine\ORM\Mapping\Driver\DriverChain');
		}

		$chainDriverDef = $container->getDefinition($entityManagerName . '.metadataDriver');

		foreach ($this->drivers as $driverType => $driverPaths) {
			$mappingService = $entityManagerName . '.' . $driverType . '.metadataDriver';
			if (!$container->hasDefinition($mappingService)) {
				$container->addDefinition($mappingService);
			}

			$mappingDriverDef = $container->getDefinition($mappingService);
			$mappingDriverDef->addSetup('addPaths', array(array_values($driverPaths)));

			if ($driverType == 'annotation') {
				$mappingDriverDef->setClass($this->metadataDriverClasses[$driverType], array(1 => NULL));

			} else {
				$mappingDriverDef->setClass($this->metadataDriverClasses[$driverType], array(NULL));
			}

			if (in_array($driverType, array('yml', 'xml'))) {
				$mappingDriverDef->addSetup('setNamespacePrefixes', array(array_flip($driverPaths)));
				$mappingDriverDef->addSetup('setGlobalBasename', array('mapping'));
			}

			foreach ($driverPaths as $prefix => $driverPath) {
				$chainDriverDef->addSetup('addDriver', array('@' . $mappingService, $prefix));
			}
		}

		$this->registerKdybyEntities($chainDriverDef, $config);
	}



	/**
	 * Loads a configured entity managers cache drivers.
	 *
	 * @param array $config
	 */
	protected function loadOrmCacheDrivers(array $config)
	{
		$this->loadOrmEntityManagerCacheDriver($config, 'metadataCache');
		$this->loadOrmEntityManagerCacheDriver($config, 'resultCache');
		$this->loadOrmEntityManagerCacheDriver($config, 'queryCache');
	}



	/**
	 * Loads a configured entity managers metadata, query or result cache driver.
	 *
	 * @param array	$config A configured ORM entity manager.
	 * @param string $cacheName
	 */
	protected function loadOrmEntityManagerCacheDriver(array $config, $cacheName)
	{
		$container = $this->getContainerBuilder();
		$entityManagerName = 'doctrine.orm.' . $config['name'] . 'EntityManager';
		$cacheDriver = $config[$cacheName . "Driver"];

		if (is_string($cacheDriver)) {
			$cacheDriver = array('type' => $cacheDriver);
		}

		// generate an unique namespace for the given application
		$namespace = 'orm.' . $config['name'] . '.' . $cacheName . '.' . md5($container->parameters['appDir'] . $container->parameters['environment']);

		// create cache
		if ($cacheDriver['type'] === 'memcache') {
			$options = self::getOptions($cacheDriver, $this->memcacheDefaults);

			$container->addDefinition($entityManagerName . '.memcacheInstance')
				->setClass($options['instanceClass'])
				->addSetup('connect', array($options['host'], $options['port']));

			$container->addDefinition($entityManagerName . '.' . $cacheName)
				->setClass($this->cacheDriverClasses[$cacheDriver['type']])
				->addSetup('setMemcache', array('@' . $entityManagerName . '.memcacheInstance'))
				->addSetup('setNamespace', array($namespace));

		} elseif (in_array($cacheDriver['type'], array('apc', 'array', 'xcache'))) {
			$container->addDefinition($entityManagerName . '.' . $cacheName)
				->setClass($this->cacheDriverClasses[$cacheDriver['type']])
				->addSetup('setNamespace', array($namespace));

		} elseif ($cacheDriver['type'] === 'file') {
			$container->addDefinition($entityManagerName . '.' . $cacheName)
				->setClass($this->cacheDriverClasses['file'], array('@cacheStorage'))
				->addSetup('setNamespace', array($namespace));

		} else {
			throw new Kdyby\InvalidStateException($config['name'] . 'EntityManager: unknown cache driver type "' . $cacheDriver['type'] . '" for "' . $cacheDriver . '".');
		}
	}



	/**
	 * @param array $mappingConfig
	 * @param array $config
	 * @return mixed
	 * @throws \Kdyby\InvalidArgumentException
	 */
	protected function loadPackageMappingInformation(array $mappingConfig, array $config)
	{
		if ($mappingConfig['package']) {
			if (!$mappingConfig = $this->getPackageMappingDriverConfigDefaults($mappingConfig)) {
				return;
			}
		}

		$this->assertValidMappingConfiguration($mappingConfig, $config);
		$this->setMappingDriverConfig($mappingConfig, $config);
		$this->aliasMap[$mappingConfig['alias']] = $mappingConfig['prefix'];
	}



	/**
	 * If this is a package controlled mapping all the missing information can be autodetected by this method.
	 *
	 * Returns false when autodetection failed, an array of the completed information otherwise.
	 *
	 * @param array $config
	 *
	 * @return array|bool
	 */
	protected function getPackageMappingDriverConfigDefaults(array $config)
	{
		$package = $this->packages[$config['name']];
		$packageDir = $package->getPath();

		// mapping type
		$config['type'] = $config['type'] ?: $this->detectMetadataDriver($packageDir);
		if (!$config['type']) {
			return FALSE;
		}

		if (!$config['dir']) {
			if (!in_array($config['type'], array('annotation', 'staticphp'))) {
				$config['dir'] = $packageDir . '/Resources/config/doctrine';

			} else {
				$config['dir'] = $packageDir . '/Entity';
			}

		} else {
			$config['dir'] = realpath($packageDir . '/' . $config['dir']);
		}

		// prefix
		$entityNamespaces = $package->getEntityNamespaces();
		$config['prefix'] = $config['prefix'] ?: reset($entityNamespaces);

		return $config;
	}



	/**
	 * Assertion if the specified mapping information is valid.
	 *
	 * @param array $mappingConfig
	 * @param array $config
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 */
	protected function assertValidMappingConfiguration(array $mappingConfig, array $config)
	{
		if (!$mappingConfig['type'] || !$mappingConfig['dir'] || !$mappingConfig['prefix']) {
			throw new Kdyby\InvalidArgumentException($config['name'] . 'EntityManager: mapping definitions require at least the "type", "dir" and "prefix" options.');
		}

		if (!file_exists($mappingConfig['dir'])) {
			throw new Kdyby\InvalidArgumentException($config['name'] . 'EntityManager: specified non-existing directory "' . $mappingConfig['dir'] . '" as Doctrine mapping source.');
		}

		if (!in_array($mappingConfig['type'], array('xml', 'yml', 'annotation', 'php', 'staticphp'))) {
			$drivers = array_keys($this->metadataDriverClasses);
			$lastDriver = array_pop($drivers);

			throw new Kdyby\InvalidArgumentException($config['name'] . 'EntityManager: can only configure ' .
					'"' . implode('", "', $drivers) . '" or "' . $lastDriver . '"' .
					' through the DoctrinePackage. Use your own package to configure other metadata drivers. ' .
					'You can register them by adding a a new driver to the ' .
					'"doctrine.orm.' . $config['name'] . 'EntityManager.metadataDriver" service definition.'
			);
		}
	}



	/**
	 * Register the mapping driver configuration for later use with the object managers metadata driver chain.
	 *
	 * @param array $mappingConfig
	 * @param array $config
	 *
	 * @return void
	 */
	protected function setMappingDriverConfig(array $mappingConfig, array $config)
	{
		if (!is_dir($mappingConfig['dir'])) {
			throw new Kdyby\InvalidArgumentException($config['name'] . 'EntityManager: invalid mapping path given. Cannot load mapping/package named "' . $mappingConfig['name'] . '".');
		}

		$this->drivers[$mappingConfig['type']][$mappingConfig['prefix']] = realpath($mappingConfig['dir']);
	}



	/**
	 * @param \Nette\DI\ServiceDefinition $chainDriverDef
	 * @param array $config
	 * @return void
	 */
	private function registerKdybyEntities(Nette\DI\ServiceDefinition $chainDriverDef, $config)
	{
		$container = $this->getContainerBuilder();

		// gather paths
		$paths = array();
		$packages = $container->parameters['kdyby']['packages'];
		if (in_array('Kdyby\Package\CmsPackage\CmsPackage', $packages)) {
			$paths[] = dirname(ClassType::from('Kdyby\CMS')->getFileName());
		}
		if (in_array('Kdyby\Package\FrameworkPackage\FrameworkPackage', $packages)) {
			$paths[] = dirname(ClassType::from('Kdyby\Framework')->getFileName());
		}
		if (!$paths) {
			return;
		}

		// create definition
		$mappingService = 'doctrine.orm.' . $config['name'] . 'EntityManager.kdybyAnnotation.metadataDriver';
		$container->addDefinition($mappingService)
			->setClass($this->metadataDriverClasses['annotation'])
			->addSetup('addPaths', array($paths));

		// add to chain
		$chainDriverDef->addSetup('addDriver', array('@' . $mappingService, 'Kdyby'));
	}



	/**
	 * Detects what metadata driver to use for the supplied directory.
	 *
	 * @param string $packageDir	   A directory path
	 *
	 * @return string|null A metadata driver short name, if one can be detected
	 */
	protected function detectMetadataDriver($packageDir)
	{
		// add the closest existing directory as a resource
		$resource = $packageDir . '/Resources/config/doctrine';
		if (($files = glob($resource . '/*.orm.xml')) && count($files)) {
			return 'xml';

		} elseif (($files = glob($resource . '/*.orm.yml')) && count($files)) {
			return 'yml';

		} elseif (($files = glob($resource . '/*.orm.php')) && count($files)) {
			return 'php';

		} elseif (is_dir($packageDir . '/Entity')) {
			return 'annotation';
		}

		return NULL;
	}



	/**
	 * @param string $name
	 *
	 * @return \Nette\Reflection\ClassType
	 */
	protected function getPackageReflectionByName($name)
	{
		$container = $this->getContainerBuilder();
		if (!isset($container->parameters['kdyby']['packages'][$name])) {
			return NULL;
		}

		return ClassType::from($container->parameters['kdyby']['packages'][$name]);
	}

}
