<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages\DoctrinePackage\DI;

use Kdyby;
use Kdyby\DI\Loader\NeonFileLoader;
use Nette\Reflection\ClassType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;



/**
 * DoctrineExtension is an extension for the Doctrine DBAL and ORM library.
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DoctrineExtension extends Kdyby\DI\Extension
{

	/**
	 * Used inside metadata driver method to simplify aggregation of data.
	 *
	 * @var array
	 */
	protected $aliasMap = array();

	/**
	 * Used inside metadata driver method to simplify aggregation of data.
	 *
	 * @var array
	 */
	protected $drivers = array();

	/** @var string */
	private $defaultConnection;

	/** @var array */
	private $entityManagers = array();

	/** @var array */
	private $defaultTypes = array(
		Kdyby\Doctrine\Type::CALLBACK => 'Kdyby\Doctrine\Types\Callback',
		Kdyby\Doctrine\Type::PASSWORD => 'Kdyby\Doctrine\Types\Password'
	);



	/**
	 * @param array $configs
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration($container->getParameter('productionMode'));
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new NeonFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
		$loader->load('annotation.neon');
		$loader->load('doctrine.neon');

		$this->dbalLoad($config['dbal'], $container);
		$this->ormLoad($config['orm'], $container);
	}



	/**
	 * Loads the DBAL configuration.
	 *
	 * @param array			$config	An array of configuration settings
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
	 */
	protected function dbalLoad(array $config, ContainerBuilder $container)
	{
		if (empty($config['default_connection'])) {
			$keys = array_keys($config['connections']);
			$config['default_connection'] = reset($keys);
		}
		$this->defaultConnection = $config['default_connection'];

		$container->setAlias('database_connection', 'doctrine.dbal.' . $this->defaultConnection . '_connection');
		$container->setAlias('doctrine.dbal.event_manager', new Alias('doctrine.dbal.' . $this->defaultConnection . '_connection.event_manager', FALSE));

		$container->setParameter('doctrine.dbal.connection_factory.types', $config['types'] + $this->defaultTypes);

		$connections = array();
		foreach (array_keys($config['connections']) as $name) {
			$connections[$name] = 'doctrine.dbal.' . $name . '_connection';
		}
		$container->setParameter('doctrine.connections', $connections);
		$container->setParameter('doctrine.default_connection', $this->defaultConnection);

		foreach ($config['connections'] as $name => $connection) {
			$this->loadDbalConnection($name, $connection, $container);
		}
	}



	/**
	 * Loads a configured DBAL connection.
	 *
	 * @param string		   $name	   The name of the connection
	 * @param array			$connection A dbal connection configuration.
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container  A ContainerBuilder instance
	 */
	protected function loadDbalConnection($name, array $connection, ContainerBuilder $container)
	{
		// configuration
		$configurationName = 'doctrine.dbal.' . $name . '_connection.configuration';
		$configuration = $container->setDefinition($configurationName, new DefinitionDecorator('doctrine.dbal.connection.configuration'));
		if (isset($connection['logging']) && $connection['logging']) {
			$configuration->addMethodCall('setSQLLogger', array(new Reference('doctrine.dbal.logger')));
			unset($connection['logging']);
		}

		// event manager
		$evmName = 'doctrine.dbal.' . $name . '_connection.event_manager';
		$evm = $container->setDefinition($evmName, new DefinitionDecorator('doctrine.dbal.connection.event_manager'));

		// charset
		if (isset($connection['charset']) && $this->connectionUsesMysqlDriver($connection)) {
			$mysqlSessionInit = new DefinitionDecorator('doctrine.dbal.events.mysql_session_init');
			$mysqlSessionInit->setArguments(array($connection['charset']));

			$container->setDefinition('doctrine.dbal.' . $name . '_connection.events.mysqlsessioninit', $mysqlSessionInit);
			unset($connection['charset']);
		}

		// connection
		$connName = 'doctrine.dbal.' . $name . '_connection';
		$conn = $container->setDefinition($connName, new DefinitionDecorator('doctrine.dbal.connection.abstract'));
		$conn->setArguments(array(
			$this->getConnectionOptions($connection),
			new Reference($configurationName),
			new Reference($evmName),
			$connection['mapping_types']
		));

		$container->setAlias('doctrine.dbal.connection', $connName);
	}



	/**
	 * @param array $connection
	 *
	 * @return boolean
	 */
	protected function connectionUsesMysqlDriver(array $connection)
	{
		return (isset($connection['driver']) && stripos($connection['driver'], 'mysql') !== FALSE)
			|| (isset($connection['driver_class']) && stripos($connection['driver_class'], 'mysql') !== FALSE);
	}



	/**
	 * @param array $connection
	 *
	 * @return array
	 */
	protected function getConnectionOptions($connection)
	{
		$options = $connection;

		if (isset($options['platform_service'])) {
			$options['platform'] = new Reference($options['platform_service']);
			unset($options['platform_service']);
		}
		unset($options['mapping_types']);

		$rename = array(
			'options' => 'driverOptions',
			'driver_class' => 'driverClass',
			'wrapper_class' => 'wrapperClass',
		);

		foreach ($rename as $old => $new) {
			if (isset($options[$old])) {
				$options[$new] = $options[$old];
				unset($options[$old]);
			}
		}

		return $options;
	}



	/**
	 * Loads the Doctrine ORM configuration.
	 *
	 * @param array			$config	An array of configuration settings
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
	 */
	protected function ormLoad(array $config, ContainerBuilder $container)
	{
		$this->entityManagers = array();
		foreach (array_keys($config['entity_managers']) as $name) {
			$this->entityManagers[$name] = 'doctrine.orm.' . $name . '_entity_manager';
		}
		$container->setParameter('doctrine.entity_managers', $this->entityManagers);

		if (empty($config['default_entity_manager'])) {
			$tmp = array_keys($this->entityManagers);
			$config['default_entity_manager'] = reset($tmp);
		}
		$container->setParameter('doctrine.default_entity_manager', $config['default_entity_manager']);

		$proxyOptions = array('auto_generate_proxy_classes', 'proxy_dir', 'proxy_namespace');
		foreach ($proxyOptions as $key) {
			$container->setParameter('doctrine.orm.' . $key, $config[$key]);
		}

		$container->setAlias('doctrine.orm.entity_manager', 'doctrine.orm.' . $config['default_entity_manager'] . '_entity_manager');

		foreach ($config['entity_managers'] as $name => $entityManager) {
			$entityManager['name'] = $name;
			$this->loadOrmEntityManager($entityManager, $container);
		}
	}



	/**
	 * Loads a configured ORM entity manager.
	 *
	 * @param array $entityManager A configured ORM entity manager.
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
	 */
	protected function loadOrmEntityManager(array $entityManager, ContainerBuilder $container)
	{
		if ($entityManager['auto_mapping'] && count($this->entityManagers) > 1) {
			throw new Kdyby\InvalidStateException('You cannot enable "auto_mapping" when several entity managers are defined.');
		}

		$configurationName = 'doctrine.orm.' . $entityManager['name'] . '_configuration';
		$configuration = $container->setDefinition($configurationName, new DefinitionDecorator('doctrine.orm.configuration'));

		$this->loadOrmEntityManagerMappingInformation($entityManager, $configuration, $container);
		$this->loadOrmCacheDrivers($entityManager, $container);

		$methods = array(
			'setMetadataCacheImpl' => new Reference('doctrine.orm.' . $entityManager['name'] . '_metadata_cache'),
			'setQueryCacheImpl' => new Reference('doctrine.orm.' . $entityManager['name'] . '_query_cache'),
			'setResultCacheImpl' => new Reference('doctrine.orm.' . $entityManager['name'] . '_result_cache'),
			'setMetadataDriverImpl' => new Reference('doctrine.orm.' . $entityManager['name'] . '_metadata_driver'),
			'setProxyDir' => '%doctrine.orm.proxy_dir%',
			'setProxyNamespace' => '%doctrine.orm.proxy_namespace%',
			'setAutoGenerateProxyClasses' => '%doctrine.orm.auto_generate_proxy_classes%',
			'setClassMetadataFactoryName' => $entityManager['class_metadata_factory_name'],
		);
		foreach ($methods as $method => $arg) {
			$configuration->addMethodCall($method, array($arg));
		}

		// hydrators
		foreach ($entityManager['hydrators'] as $name => $class) {
			$configuration->addMethodCall('addCustomHydrationMode', array($name, $class));
		}

		// dql functions
		if (!empty($entityManager['dql'])) {
			foreach ($entityManager['dql']['string_functions'] as $name => $function) {
				$configuration->addMethodCall('addCustomStringFunction', array($name, $function));
			}
			foreach ($entityManager['dql']['numeric_functions'] as $name => $function) {
				$configuration->addMethodCall('addCustomNumericFunction', array($name, $function));
			}
			foreach ($entityManager['dql']['datetime_functions'] as $name => $function) {
				$configuration->addMethodCall('addCustomDatetimeFunction', array($name, $function));
			}
		}

		if (!isset($entityManager['connection'])) {
			$entityManager['connection'] = $this->defaultConnection;
		}

		// entity manager
		$container->setDefinition(
			'doctrine.orm.' . $entityManager['name'] . '_entity_manager',
			new DefinitionDecorator('doctrine.orm.entity_manager.abstract')
		)->setArguments(array(
			new Reference('doctrine.dbal.' . $entityManager['connection'] . '_connection'),
			new Reference('doctrine.orm.' . $entityManager['name'] . '_configuration')
		));

		// event manager
		$container->setAlias(
			'doctrine.orm.' . $entityManager['name'] . '_entity_manager.event_manager',
			new Alias('doctrine.dbal.' . $entityManager['connection'] . '_connection.event_manager', FALSE)
		);

		$this->loadOrmFixtures($container, $entityManager);
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param array $entityManagerName
	 */
	private function loadOrmFixtures(ContainerBuilder $container, array $entityManager)
	{
		if (!class_exists('Doctrine\Common\DataFixtures\Loader')) {
			return; // todo: throw?
		}

		$entityManagerName = 'doctrine.orm.' . $entityManager['name'] . '_entity_manager';
		$prefix = $entityManagerName . '.data_fixtures';

		$loader = new Definition('%doctrine.orm.data_fixtures.loader.class%');
		$container->setDefinition($prefix . '.loader', $loader);

		$purger = new Definition('%doctrine.orm.data_fixtures.purger.class%');
		$purger->setArguments(array(new Reference($entityManagerName)));
		$container->setDefinition($prefix . '.purger', $purger);

		$executor = new Definition('%doctrine.orm.data_fixtures.executor.class%');
		$executor->setArguments(array(
			new Reference($entityManagerName),
			new Reference($prefix . '.purger')
		));
		$container->setDefinition($prefix . '.executor', $executor);
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
	 * @param array $entityManager A configured ORM entity manager.
	 * @param \Symfony\Component\DependencyInjection\Definition $configuration
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
	 */
	protected function loadOrmEntityManagerMappingInformation(array $entityManager, Definition $configuration, ContainerBuilder $container)
	{
		// reset state of drivers and alias map. They are only used by this methods and children.
		$this->drivers = array();
		$this->aliasMap = array();

		$this->loadMappingInformation($entityManager, $container);
		$this->registerMappingDrivers($entityManager, $container);

		$configuration->addMethodCall('setEntityNamespaces', array($this->aliasMap));
	}



	/**
	 * Loads a configured entity managers cache drivers.
	 *
	 * @param array			$entityManager A configured ORM entity manager.
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container	 A ContainerBuilder instance
	 */
	protected function loadOrmCacheDrivers(array $entityManager, ContainerBuilder $container)
	{
		$this->loadOrmEntityManagerCacheDriver($entityManager, $container, 'metadata_cache');
		$this->loadOrmEntityManagerCacheDriver($entityManager, $container, 'result_cache');
		$this->loadOrmEntityManagerCacheDriver($entityManager, $container, 'query_cache');
	}



	/**
	 * Loads a configured entity managers metadata, query or result cache driver.
	 *
	 * @param array			$entityManager A configured ORM entity manager.
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
	 * @param string		   $cacheName
	 */
	protected function loadOrmEntityManagerCacheDriver(array $entityManager, ContainerBuilder $container, $cacheName)
	{
		$cacheDriver = $entityManager[$cacheName . "_driver"];
		$namespace = 'orm_' . $entityManager['name'] . '_' . $cacheName . '_' . md5($container->getParameter('appDir') . $container->getParameter('environment'));

		switch ($cacheDriver['type']) {
			case 'memcache':
				$memcacheClass = !empty($cacheDriver['class']) ? $cacheDriver['class'] : '%doctrine.orm.cache.memcache.class%';
				$memcacheInstanceClass = !empty($cacheDriver['instance_class']) ? $cacheDriver['instance_class'] : '%doctrine.orm.cache.memcache_instance.class%';
				$memcacheHost = !empty($cacheDriver['host']) ? $cacheDriver['host'] : '%doctrine.orm.cache.memcache_host%';
				$memcachePort = !empty($cacheDriver['port']) ? $cacheDriver['port'] : '%doctrine.orm.cache.memcache_port%';

				$cacheDef = new Definition($memcacheClass);
				$memcacheInstance = new Definition($memcacheInstanceClass);
				$memcacheInstance->addMethodCall('connect', array(
					$memcacheHost, $memcachePort
				));
				$container->setDefinition('doctrine.orm.' . $entityManager['name'] . '_memcache_instance', $memcacheInstance);
				$cacheDef->addMethodCall('setMemcache', array(new Reference('doctrine.orm.' . $entityManager['name'] . '_memcache_instance')));
				$cacheDef->addMethodCall('setNamespace', array($namespace));
				break;

			case 'apc':
			case 'array':
			case 'xcache':
				$cacheDef = new Definition('%doctrine.orm.cache.' . $cacheDriver['type'] . '.class%');
				$cacheDef->addMethodCall('setNamespace', array($namespace));
				break;

			default:
				$cacheDef = new DefinitionDecorator('doctrine.cache');
		}

		$cacheDef->setPublic(FALSE);
		// generate a unique namespace for the given application

		$container->setDefinition('doctrine.orm.' . $entityManager['name'] . '_' . $cacheName, $cacheDef);
	}



	/**
	 * @param array			$objectManager A configured object manager.
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container	 A ContainerBuilder instance
	 */
	protected function loadMappingInformation(array $objectManager, ContainerBuilder $container)
	{
		if ($objectManager['auto_mapping']) {
			// automatically register package mappings
			foreach (array_keys($container->getParameter('kdyby.packages')) as $package) {
				if (!isset($objectManager['mappings'][$package])) {
					$objectManager['mappings'][$package] = NULL;
				}
			}
		}

		foreach ($objectManager['mappings'] as $mappingName => $mappingConfig) {
			if ($mappingConfig !== NULL && $mappingConfig['mapping'] !== FALSE) {
				continue;
			}

			$mappingConfig = array_replace(array(
				'dir' => FALSE,
				'type' => FALSE,
				'prefix' => FALSE,
			), (array)$mappingConfig);

			$this->loadPackageMappingInformation($mappingName, $mappingConfig, $objectManager, $container);
		}
	}



	/**
	 * @param string $name
	 * @param array $config
	 * @param array	$objectManager
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	protected function loadPackageMappingInformation($name, array $config, array $objectManager, ContainerBuilder $container)
	{
		$config['dir'] = $container->getParameterBag()->resolveValue($config['dir']);

		// a package configuration is detected by realizing that the specified dir is not absolute and existing
		if (!isset($config['is_package'])) {
			$config['is_package'] = !file_exists($config['dir']);
		}

		if ($config['is_package']) {
			if (NULL === $package = $this->getPackageReflectionByName($container, $name)) {
				throw new Kdyby\InvalidArgumentException('Package "' . $name . '" does not exist or it is not enabled.');
			}

			if (!$config = $this->getMappingDriverPackageConfigDefaults($config, $package, $container)) {
				return;
			}
		}

		$this->assertValidMappingConfiguration($config, $objectManager['name']);
		$this->setMappingDriverConfig($config, $name);
		$this->setMappingDriverAlias($config, $name);
	}



	/**
	 * If this is a package controlled mapping all the missing information can be autodetected by this method.
	 *
	 * Returns false when autodetection failed, an array of the completed information otherwise.
	 *
	 * @param array			$packageConfig
	 * @param \Nette\Reflection\ClassType $package
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container	A ContainerBuilder instance
	 *
	 * @return array|false
	 */
	protected function getMappingDriverPackageConfigDefaults(array $packageConfig, ClassType $package, ContainerBuilder $container)
	{
		$packageDir = dirname($package->getFilename());

		if (!$packageConfig['type']) {
			$packageConfig['type'] = $this->detectMetadataDriver($packageDir, $container);
		}

		if (!$packageConfig['type']) {
			return;
		}

		if (!$packageConfig['dir']) {
			if (!in_array($packageConfig['type'], array('annotation', 'staticphp'))) {
				$packageConfig['dir'] = $packageDir . '/Resources/config/doctrine';

			} else {
				$packageConfig['dir'] = $packageDir . '/Entity';
			}

		} else {
			$packageConfig['dir'] = $packageDir . '/' . $packageConfig['dir'];
		}

		if (!$packageConfig['prefix']) {
			$packageConfig['prefix'] = $package->getNamespaceName() . '\\Entity';
		}

		return $packageConfig;
	}



	/**
	 * Assertion if the specified mapping information is valid.
	 *
	 * @param array  $mappingConfig
	 * @param string $objectManagerName
	 */
	protected function assertValidMappingConfiguration(array $mappingConfig, $objectManagerName)
	{
		if (!$mappingConfig['type'] || !$mappingConfig['dir'] || !$mappingConfig['prefix']) {
			throw new Kdyby\InvalidArgumentException('Mapping definitions for Doctrine manager "' . $objectManagerName . '" require at least the "type", "dir" and "prefix" options.');
		}

		if (!file_exists($mappingConfig['dir'])) {
			throw new Kdyby\InvalidArgumentException('Specified non-existing directory "' . $mappingConfig['dir'] . '" as Doctrine mapping source.');
		}

		if (!in_array($mappingConfig['type'], array('xml', 'yml', 'annotation', 'php', 'staticphp'))) {
			throw new Kdyby\InvalidArgumentException('Can only configure "xml", "yml", "annotation", "php" or ' .
					'"staticphp" through the DoctrinePackage. Use your own package to configure other metadata drivers. ' .
					'You can register them by adding a a new driver to the ' .
					'"doctrine.orm.' . $objectManagerName . '.metadata_driver" service definition.'
			);
		}
	}



	/**
	 * Register the mapping driver configuration for later use with the object managers metadata driver chain.
	 *
	 * @param array  $mappingConfig
	 * @param string $mappingName
	 *
	 * @return void
	 */
	protected function setMappingDriverConfig(array $mappingConfig, $mappingName)
	{
		if (is_dir($mappingConfig['dir'])) {
			$this->drivers[$mappingConfig['type']][$mappingConfig['prefix']] = realpath($mappingConfig['dir']);
		} else {
			throw new Kdyby\InvalidArgumentException('Invalid Doctrine mapping path given. Cannot load Doctrine mapping/package named "' . $mappingName . '".');
		}
	}



	/**
	 * Register the alias for this mapping driver.
	 *
	 * Aliases can be used in the Query languages of all the Doctrine object managers to simplify writing tasks.
	 *
	 * @param array $mappingConfig
	 * @param string $mappingName
	 *
	 * @return void
	 */
	protected function setMappingDriverAlias($mappingConfig, $mappingName)
	{
		if (isset($mappingConfig['alias'])) {
			$this->aliasMap[$mappingConfig['alias']] = $mappingConfig['prefix'];
		} else {
			$this->aliasMap[$mappingName] = $mappingConfig['prefix'];
		}
	}



	/**
	 * Register all the collected mapping information with the object manager by registering the appropriate mapping drivers.
	 *
	 * @param array			$objectManager
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container	 A ContainerBuilder instance
	 */
	protected function registerMappingDrivers($objectManager, ContainerBuilder $container)
	{
		$managerName = 'doctrine.orm.' . $objectManager['name'];

		// configure metadata driver for each package based on the type of mapping files found
		if ($container->hasDefinition($managerName . '_metadata_driver')) {
			$chainDriverDef = $container->getDefinition($managerName . '_metadata_driver');

		} else {
			$chainDriverDef = new Definition('%doctrine.orm.metadata.driver_chain.class%');
			$chainDriverDef->setPublic(FALSE);
		}

		foreach ($this->drivers as $driverType => $driverPaths) {
			$mappingService = $managerName . '_' . $driverType . '_metadata_driver';

			if ($container->hasDefinition($mappingService)) {
				$mappingDriverDef = $container->getDefinition($mappingService);
				$args = $mappingDriverDef->getArguments();
				if ($driverType == 'annotation') {
					$args[1] = array_merge(array_values($driverPaths), $args[1]);
				} else {
					$args[0] = array_merge(array_values($driverPaths), $args[0]);
				}
				$mappingDriverDef->setArguments($args);

			} elseif ($driverType == 'annotation') {
				$mappingDriverDef = new Definition('%doctrine.orm.metadata.' . $driverType . '.class%', array(
					new Reference('doctrine.orm.metadata.annotation_reader'),
					array_values($driverPaths)
				));

			} else {
				$mappingDriverDef = new Definition('%doctrine.orm.metadata.' . $driverType . '.class%', array(
					array_values($driverPaths)
				));
			}
			$mappingDriverDef->setPublic(FALSE);

			if (FALSE !== strpos($mappingDriverDef->getClass(), 'yml') || FALSE !== strpos($mappingDriverDef->getClass(), 'xml')) {
				$mappingDriverDef->addMethodCall('setNamespacePrefixes', array(array_flip($driverPaths)));
				$mappingDriverDef->addMethodCall('setGlobalBasename', array('mapping'));
			}

			$container->setDefinition($mappingService, $mappingDriverDef);

			foreach ($driverPaths as $prefix => $driverPath) {
				$chainDriverDef->addMethodCall('addDriver', array(new Reference($mappingService), $prefix));
			}
		}

		$this->registerKdybyEntities($chainDriverDef, $container, $managerName);
		$container->setDefinition($managerName . '_metadata_driver', $chainDriverDef);
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\Definition $chainDriverDef
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param string $managerName
	 * @return void
	 */
	private function registerKdybyEntities(Definition $chainDriverDef, ContainerBuilder $container, $managerName)
	{
		// gather paths
		$paths = array();
		$packages = $container->getParameter('kdyby.packages');
		if (in_array('Kdyby\Packages\CmsPackage\CmsPackage', $packages)) {
			$paths[] = dirname(ClassType::from('Kdyby\CMS')->getFileName());
		}
		if (in_array('Kdyby\Packages\FrameworkPackage\FrameworkPackage', $packages)) {
			$paths[] = dirname(ClassType::from('Kdyby\Framework')->getFileName());
		}
		if (!$paths) {
			return;
		}

		// create definition
		$driverDef = new Definition('%doctrine.orm.metadata.annotation.class%', array(
			new Reference('doctrine.orm.metadata.annotation_reader'),
			$paths
		));
		$driverDef->setPublic(FALSE);

		// register
		$mappingService = $managerName . '_annotation_kdyby_metadata_driver';
		$container->setDefinition($mappingService, $driverDef);

		// add to chain
		$chainDriverDef->addMethodCall('addDriver', array(new Reference($mappingService), 'Kdyby'));
	}



	/**
	 * Detects what metadata driver to use for the supplied directory.
	 *
	 * @param string		   $dir	   A directory path
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container A ContainerBuilder instance
	 *
	 * @return string|null A metadata driver short name, if one can be detected
	 */
	protected function detectMetadataDriver($dir, ContainerBuilder $container)
	{
		// add the closest existing directory as a resource
		$resource = $dir . '/Resources/config/doctrine';
		if (($files = glob($resource . '/*.orm.xml')) && count($files)) {
			return 'xml';

		} elseif (($files = glob($resource . '/*.orm.yml')) && count($files)) {
			return 'yml';

		} elseif (($files = glob($resource . '/*.orm.php')) && count($files)) {
			return 'php';

		} elseif (is_dir($dir . '/Entity')) {
			return 'annotation';
		}

		return null;
	}



	/**
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 * @param string $name
	 */
	protected function getPackageReflectionByName(ContainerBuilder $container, $name)
	{
		$packages = $container->getParameter('kdyby.packages');
		if (!isset($packages[$name])) {
			return NULL;
		}

		return ClassType::from($packages[$name]);
	}



	/**
	 * Returns the base path for the XSD files.
	 *
	 * @return string The XSD base path
	 */
	public function getXsdValidationBasePath()
	{
		return __DIR__ . '/../Resources/config/schema';
	}



	/**
	 * Returns the namespace to be used for this extension (XML namespace).
	 *
	 * @return string The XML namespace
	 */
	public function getNamespace()
	{
		return 'http://kdyby.org/schema/dic/doctrine';
	}

}
