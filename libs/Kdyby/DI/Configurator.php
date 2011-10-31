<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Doctrine;
use Doctrine\Common\Annotations;
use Doctrine\DBAL\Tools\Console\Command as DbalCommand;
use Doctrine\ORM\Tools\Console\Command as OrmCommand;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Kdyby;
use Kdyby\DI\ContainerHelper;
use Kdyby\Tools\FreezableArray;
use Nette;
use Nette\Application\Routers;
use Nette\Application\UI\Presenter;
use Nette\DI\Container as NContainer;
use Symfony\Component\Console;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Container $container
 */
class Configurator extends Nette\Configurator
{

	/**
	 * @param string $containerClass
	 */
	public function __construct($containerClass = 'Kdyby\DI\Container')
	{
		parent::__construct($containerClass);

		$baseUrl = rtrim($this->container->httpRequest->getUrl()->getBaseUrl(), '/');
		$this->container->params['baseUrl'] = $baseUrl;
		$this->container->params['basePath'] = preg_replace('#https?://[^/]+#A', '', $baseUrl);
		$this->container->params['kdybyFrameworkDir'] = realpath(KDYBY_FRAMEWORK_DIR);
	}



	/**
	 * @param NContainer $container
	 * @param array $options
	 * @return Kdyby\Application\Application
	 */
	public static function createServiceApplication(NContainer $container, array $options = NULL)
	{
		$context = new Container;
		$context->addService('httpRequest', $container->httpRequest);
		$context->addService('httpResponse', $container->httpResponse);
		$context->addService('session', $container->session);
		$context->addService('presenterFactory', $container->presenterFactory);
		$context->addService('router', $container->router);
		$context->lazyCopy('requestManager', $container);
		$context->lazyCopy('console', $container);

		Presenter::$invalidLinkMode = $container->getParam('productionMode', TRUE)
			? Presenter::INVALID_LINK_SILENT : Presenter::INVALID_LINK_WARNING;

		$application = new Kdyby\Application\Application($context);
		$application->catchExceptions = $container->getParam('productionMode', TRUE);
		$application->errorPresenter = 'Error';

		return $application;
	}



	/**
	 * @param NContainer $container
	 * @return Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(NContainer $container)
	{
		return new Kdyby\Application\PresenterFactory($container->moduleRegistry, $container);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Templates\ITemplateFactory
	 */
	public static function createServiceTemplateFactory(Container $container)
	{
		return new Kdyby\Templates\TemplateFactory($container->latteEngine);
	}



	/**
	 * @param NContainer $container
	 * @return Kdyby\Caching\FileStorage
	 */
	public static function createServiceCacheStorage(NContainer $container)
	{
		if (!isset($container->params['tempDir'])) {
			throw new Nette\InvalidStateException("Service cacheStorage requires that parameter 'tempDir' contains path to temporary directory.");
		}
		$dir = $container->expand('%tempDir%/cache');
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Kdyby\Caching\FileStorage($dir, $container->cacheJournal);
	}



	/**
	 * @param NContainer $container
	 * @return Kdyby\Loaders\RobotLoader
	 */
	public static function createServiceRobotLoader(NContainer $container, array $options = NULL)
	{
		$loader = new Kdyby\Loaders\RobotLoader;
		$loader->autoRebuild = isset($options['autoRebuild']) ? $options['autoRebuild'] : !$container->params['productionMode'];
		$loader->setCacheStorage($container->cacheStorage);
		if (isset($options['directory'])) {
			$loader->addDirectory($options['directory']);
		} else {
			foreach (array('appDir', 'libsDir') as $var) {
				if (isset($container->params[$var])) {
					$loader->addDirectory($container->params[$var]);
				}
			}
		}
		$loader->register();
		return $loader;
	}



	/**
	 * @param Container $container
	 * @param array $options
	 * @return Nette\Latte\Engine
	 */
	public static function createServiceLatteEngine(Container $container, array $options = NULL)
	{
		$engine = new Nette\Latte\Engine;

		foreach ($options as $macroSetClass) {
			$macroSetClass::install($engine->parser);
		}

		return $engine;
	}



	/**
	 * @param NContainer $container
	 * @return Routers\RouteList
	 */
	public static function createServiceRouter(NContainer $container)
	{
		$router = new Routers\RouteList;

		$router[] = $backend = new Routers\RouteList('Backend');

			$backend[] = new Routers\Route('admin/[sign/in]', array(
				'presenter' => 'Sign',
				'action' => 'in',
			));

			$backend[] = new Routers\Route('admin/<presenter>[/<action>]', array(
				'action' => 'default',
			));

		return $router;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Application\RequestManager
	 */
	public static function createServiceRequestManager(Container $container)
	{
		return new Kdyby\Application\RequestManager($container->application, $container->session);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Application\ModuleCascadeRegistry
	 */
	public static function createServiceModuleRegistry(Container $container)
	{
		$register = new Kdyby\Application\ModuleCascadeRegistry;

		foreach ($container->getParam('modules', array()) as $namespace => $path) {
			$register->add($namespace, $container->expand($path));
		}

		return $register;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Components\Grinder\GridFactory
	 */
	public static function createServiceGrinderFactory(Container $container)
	{
		return new Kdyby\Components\Grinder\GridFactory($container->entityManager, $container->session);
	}


	/****************** Security ****************/



	/**
	 * @param Container $container
	 * @return Kdyby\Security\Authenticator
	 */
	public static function createServiceAuthenticator(Container $container)
	{
		return new Kdyby\Security\Authenticator($container->users);
	}



	/**
	 * @param NContainer $container
	 * @return Kdyby\Security\User
	 */
	public static function createServiceUser(NContainer $container)
	{
		$context = new Container;
		// copies services from $container and preserves lazy loading
		$context->lazyCopy('authenticator', $container);
		$context->lazyCopy('authorizator', $container);
		$context->lazyCopy('entityManager', $container);
		$context->addService('session', $container->session);

		return new Kdyby\Security\User($context);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Security\Users
	 */
	public static function createServiceUsers(Container $container)
	{
		return new Kdyby\Security\Users($container->entityManager);
	}



	/****************** Console ****************/



	/**
	 * @param Container $container
	 * @return Console\Helper\HelperSet
	 */
	public static function createServiceConsoleHelpers(Container $container)
	{
		$helperSet = new Console\Helper\HelperSet(array(
			'di' => new ContainerHelper($container),
			'em' => new EntityManagerHelper($container->entityManager),
			'db' => new ConnectionHelper($container->entityManager->getConnection()),
		));

		return $helperSet;
	}



	/**
	 * @todo split on services
	 *
	 * @param Container $container
	 * @return FreezableArray
	 */
	public static function createServiceConsoleCommands(Container $container)
	{
		return new FreezableArray(array(
			// DBAL Commands
			new DbalCommand\RunSqlCommand(),
			new DbalCommand\ImportCommand(),

			// ORM Commands
			new OrmCommand\SchemaTool\CreateCommand(),
			new OrmCommand\SchemaTool\UpdateCommand(),
			new OrmCommand\SchemaTool\DropCommand(),
			new OrmCommand\ValidateSchemaCommand(),
			new OrmCommand\GenerateProxiesCommand(),
			new OrmCommand\RunDqlCommand(),
		));
	}



	/**
	 * @todo realy catch exceptions?
	 *
	 * @param Container $container
	 * @return Console\Application
	 */
	public static function createServiceConsole(Container $container)
	{
		$name = Kdyby\Framework::NAME . " Command Line Interface";
		$cli = new Console\Application($name, Kdyby\Framework::VERSION);

		$cli->setCatchExceptions(TRUE);
		$cli->setHelperSet($container->consoleHelpers);
		$cli->addCommands($container->consoleCommands->freeze()->toArray());

		return $cli;
	}


	/****************** Doctrine ****************/



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Cache
	 */
	public static function createServiceOrmCache(Container $container)
	{
		return new Kdyby\Doctrine\Cache($container->cacheStorage);
	}



	/**
	 * @param Container $container
	 * @param array $options
	 * @return Annotations\AnnotationReader
	 */
	protected function createServiceAnnotationReader(Container $container, array $options = NULL)
	{
		$reader = new Annotations\AnnotationReader();

		// options
		self::setServiceOptions($config, $options, array(
				'defaultAnnotationNamespace' => 'Doctrine\ORM\Mapping\\',
				'ignoreNotImportedAnnotations' => TRUE,
				'enableParsePhpImports' => FALSE,
			), array('aliases', 'cache'));

		// default aliases
		$reader->setAnnotationNamespaceAlias('Doctrine\ORM\Mapping\\', 'Orm');

		// custom aliases
		if (isset($options['aliases'])) {
			foreach ($options['aliases'] as $alias => $namespace) {
				$reader->setAnnotationNamespaceAlias($namespace, $alias);
			}
		}

		// wrap
		return new Kdyby\Doctrine\Annotations\CachedReader(
				new Annotations\IndexedReader($reader),
				isset($options['cache']) ? $options['cache'] : $container->ormCache
			);
	}



	/**
	 * @todo prefix every annotation!
	 *
	 * @param Container $container
	 * @return Kdyby\Doctrine\Mapping\Driver\AnnotationDriver
	 */
	public static function createServiceOrmMetadataDriver(Container $container)
	{
		$loader = Kdyby\Loaders\SplClassLoader::getInstance();
		foreach ($loader->getTypeDirs('Doctrine\ORM') as $dir) {
			Annotations\AnnotationRegistry::registerFile($dir . '/Mapping/Driver/DoctrineAnnotations.php');
		}

		Annotations\AnnotationRegistry::registerFile(KDYBY_FRAMEWORK_DIR . '/Doctrine/Mapping/Driver/DoctrineAnnotations.php');

		return new Kdyby\Doctrine\Mapping\Driver\AnnotationDriver($container->annotationReader);
	}



	/**
	 * @param Container $container
	 * @param array $options
	 * @return Doctrine\DBAL\Logging\SQLLogger
	 */
	public static function createServiceSqlLogger(Container $container)
	{
		$logger = new Kdyby\Doctrine\Diagnostics\Panel();
		$logger->registerBarPanel(Nette\Diagnostics\Debugger::$bar);
		return $logger;
	}



	/**
	 * @param Container $container
	 * @param array $options
	 * @return Doctrine\ORM\Configuration
	 */
	public static function createServiceOrmConfiguration(Container $container, array $options = NULL)
	{
		$config = new Doctrine\ORM\Configuration;

		// options
		self::setServiceOptions($config, $options, array(
				// Metadata
				'metadataCacheImpl' => $container->ormCache,
				'metadataDriverImpl' => $container->ormMetadataDriver,
				'classMetadataFactoryName' => 'Kdyby\Doctrine\Mapping\ClassMetadataFactory',

				// queries
				'queryCacheImpl' => $container->ormCache,

				// Proxies
				'proxyDir' => $container->expand('%tempDir%/proxies'),
				'proxyNamespace' => 'Kdyby\Domain\Proxy',
				'autoGenerateProxyClasses' => $container->params['productionMode'],

				// sqlLogger
				'sQLLogger' => $container->sqlLogger
			));

		return $config;
	}



	/**
	 * @param Container $container
	 * @param array $options
	 * @return Doctrine\Common\EventManager
	 */
	public static function createServiceOrmEventManager(Container $container, array $options = NULL)
	{
		$evm = new Doctrine\Common\EventManager;

		// default listeners
		$evm->addEventSubscriber($container->ormDiscriminatorMapDiscoveryListener);
		$evm->addEventSubscriber($container->ormEntityDefaultsListener);
		// $evm->addEventSubscriber(new Kdyby\Media\Listeners\Mediable($this->context));

		// custom listeners
		foreach ($options as $listener) {
			$evm->addEventSubscriber($listener);
		}

		return $evm;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Mapping\DiscriminatorMapDiscoveryListener
	 */
	public static function createServiceOrmDiscriminatorMapDiscoveryListener(Container $container)
	{
		return new Kdyby\Doctrine\Mapping\DiscriminatorMapDiscoveryListener(
				$container->annotationReader,
				$container->ormMetadataDriver
			);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Mapping\EntityDefaultsListener
	 */
	public static function createServiceOrmEntityDefaultsListener(Container $container)
	{
		return new Kdyby\Doctrine\Mapping\EntityDefaultsListener();
	}



	/**
	 * @param Container $container
	 * @return Doctrine\DBAL\Event\Listeners\MysqlSessionInit
	 */
	public static function createServiceDbalMysqlSessionInitListener(Container $container)
	{
		return new Doctrine\DBAL\Event\Listeners\MysqlSessionInit();
	}



	/**
	 * @param Container $container
	 * @param array $options
	 * @return Doctrine\DBAL\Connection
	 */
	public static function createServiceDbalConnection(Container $container, array $options = NULL)
	{
		if (!$options) {
			throw new Nette\InvalidArgumentException("Please provide a database connection information for @dbalConnection service.");
		}

		if (isset($options['driver']) && $options['driver'] == "pdo_mysql") {
			$container->ormEventManager->addEventSubscriber($container->dbalMysqlSessionInitListener);
		}

		return Doctrine\DBAL\DriverManager::getConnection(
				$options,
				$container->ormConfiguration,
				$container->ormEventManager
			);
	}



	/**
	 * @param Container $container
	 * @return Doctrine\ORM\Tools\SchemaTool
	 */
	public static function createServiceOrmSchemaTool(Container $container)
	{
		return new Doctrine\ORM\Tools\SchemaTool($container->entityManager);
	}



	/**
	 * @param Container $container
	 * @return Doctrine\ORM\EntityManager
	 */
	public static function createServiceEntityManager(Container $container)
	{
		return Doctrine\ORM\EntityManager::create(
				$container->dbalConnection,
				$container->ormConfiguration,
				$container->ormEventManager
			);
	}


	/***************** Helpers ********************/



	/**
	 * @param object $service
	 * @param array $options
	 * @param array $defaults
	 * @param array $ignore
	 */
	public static function setServiceOptions($service, $options, array $defaults = NULL, array $ignore = NULL)
	{
		$options = Nette\Utils\Arrays::mergeTree((array)$options, $defaults);

		// set options
		foreach ($options as $name => $val) {
			$method = 'set' . strtoupper($name[0]) . substr($name, 1);
			if (!method_exists($service, $method) && !in_array($name, $ignore)) {
				throw new Nette\InvalidArgumentException("Unknown option $name", NULL,
						new Nette\MemberAccessException(
							"Call to undefined method " . get_class($service) . "::$method()."
					));
			}

			$service->$method($val);
		}
	}

}