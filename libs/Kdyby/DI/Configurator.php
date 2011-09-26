<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Doctrine\DBAL\Tools\Console\Command as DbalCommand;
use Doctrine\ORM\Tools\Console\Command as OrmCommand;
use Doctrine\CouchDB\Tools\Console\Command as CouchDBCommand;
use Doctrine\ODM\CouchDB\Tools\Console\Command as OdmCommand;
use Kdyby;
use Kdyby\Application\ModuleCascadeRegistry;
use Kdyby\DI\ContainerHelper;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\UI\Presenter;
use Symfony\Component\Console;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Container $container
 */
class Configurator extends Nette\Configurator
{

	/** @var array */
	public $onAfterLoadConfig = array();



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

		$this->onAfterLoadConfig[] = callback($this, 'loadDbSettings');
		$this->onAfterLoadConfig[] = callback($this, 'setupDebugger');
	}



	/**
	 * @param Container $container
	 */
	public function setupDebugger(Container $container)
	{
		$parameters = (array)$container->getParam('debugger', array());
		foreach ($parameters as $property => $value) {
			Nette\Utils\LimitedScope::evaluate(
				'<?php Nette\Diagnostics\Debugger::$' . $property .' = $value; ?>',
				array('value' => $value));
		}
	}



	/**
	 * Loads configuration from file and process it.
	 * @return Container
	 */
	public function loadConfig($file, $section = NULL)
	{
		parent::loadConfig($file, $section);
		$this->onAfterLoadConfig($this->container);
		return $this->container;
	}



	/**
	 * Registers CMS service and container parameters
	 * @returns Configurator
	 */
	public function registerCMS()
	{
		if (!defined('KDYBY_CMS_DIR')) {
			throw new Nette\InvalidStateException("Kdyby CMS is not present in installation.");
		}

		// make sure loader will take care
		Kdyby\Loaders\SplClassLoader::getInstance()
			->addNamespace('Kdyby', KDYBY_CMS_DIR);

		// CMS dir as expandable parameter
		$this->container->params['kdybyCmsDir'] = realpath(KDYBY_CMS_DIR);

		// register CMS container as service
		$this->container->addService('cms', new Kdyby\CMS\Container($this->container));

		// register callback for finalising this registration
		$this->onAfterLoadConfig[] = callback($this, 'finalizeCmsRegisteration');

		return $this; // fluent interface
	}



	/**
	 * Register services
	 * @internal
	 */
	public function finalizeCmsRegisteration()
	{
		// CMS modules: Backend and others
		$this->container->moduleRegistry->add('Kdyby\Modules', KDYBY_CMS_DIR . '/Modules');
	}



	/**
	 * Loads settings from database
	 */
	public function loadDbSettings()
	{
		try {
			$this->container->workspace->getClassMetadata('Kdyby\Config\Setting');
			$this->container->settings->loadAll($this->container);
		} catch (Kdyby\Doctrine\ManagerException $e) { }
	}



	/**
	 * @param Nette\DI\Container $container
	 * @param array $options
	 * @return Kdyby\Application\Application
	 */
	public static function createServiceApplication(Nette\DI\Container $container, array $options = NULL)
	{
		$context = new Container;
		$context->addService('httpRequest', $container->httpRequest);
		$context->addService('httpResponse', $container->httpResponse);
		$context->addService('session', $container->session);
		$context->addService('presenterFactory', $container->presenterFactory);
		$context->addService('router', $container->router);
		$context->lazyCopy('console', $container);

		Presenter::$invalidLinkMode = $container->getParam('productionMode', TRUE)
			? Presenter::INVALID_LINK_SILENT : Presenter::INVALID_LINK_WARNING;

		$application = new Kdyby\Application\Application($context);
		$application->catchExceptions = $container->getParam('productionMode', TRUE);
		$application->errorPresenter = 'Error';

		return $application;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\ORM\Container
	 */
	public static function createServiceSqldbContainerBuilder(Container $container)
	{
		$builder = new Kdyby\Doctrine\ORM\ContainerBuilder($container->doctrineCache, $container->getParam('sqldb', array()));
		$builder->registerTypes();
		$builder->registerAnnotationClasses();
		$builder->setProductionMode($container->params['productionMode']);
		$builder->expandParams($container);
		return $builder;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\ORM\Container
	 */
	public static function createServiceSqldb(Container $container)
	{
		return $container->sqldbContainerBuilder->build();
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\ODM\Container
	 */
	public static function createServiceCouchdb(Container $container)
	{
		return new Kdyby\Doctrine\ODM\Container($container, $container->getParam('couchdb', array()));
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Workspace
	 */
	public static function createServiceWorkspace(Container $container)
	{
		$containers = array(
			'sqldb' => $container->sqldb,
			'couchdb' => $container->couchdb
		);

		foreach ($container->getServiceNamesByTag('database') as $serviceName => $info) {
			$containers[$serviceName] = $container->getService($serviceName);
		}

		return new Kdyby\Doctrine\Workspace($containers);
	}



	/**
	 * @return Kdyby\Config\Settings
	 */
	public static function createServiceSettings(Container $container)
	{
		return new Kdyby\Config\Settings($container->workspace->getRepository('Kdyby\Config\Setting'), $container->cacheStorage);
	}



	/**
	 * @param Nette\DI\Container $container
	 * @return Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(Nette\DI\Container $container)
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
	 * @param Nette\DI\Container $container
	 * @return Kdyby\Caching\FileStorage
	 */
	public static function createServiceCacheStorage(Nette\DI\Container $container)
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
	 * @param Nette\DI\Container $container
	 * @return Kdyby\Loaders\RobotLoader
	 */
	public static function createServiceRobotLoader(Nette\DI\Container $container, array $options = NULL)
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
	 * @return Nette\Latte\Engine
	 */
	public static function createServiceLatteEngine(Container $container)
	{
		$engine = new Nette\Latte\Engine;

		foreach ($container->getParam('macros', array()) as $macroSet) {
			call_user_func(callback($macroSet), $engine->parser);
		}

		return $engine;
	}



	/**
	 * @param Nette\DI\Container $container
	 * @return Nette\Application\Routers\RouteList
	 */
	public static function createServiceRouter(Nette\DI\Container $container)
	{
		$router = new Nette\Application\Routers\RouteList;

		$router[] = $backend = new Nette\Application\Routers\RouteList('Backend');

			$backend[] = new Route('admin/[sign/in]', array(
				'presenter' => 'Sign',
				'action' => 'in',
			));

			$backend[] = new Route('admin/<presenter>[/<action>]', array(
				'action' => 'default',
			));

		foreach ($container->installWizard->getInstallers() as $installer) {
			$installer->installRoutes($router);
		}

		return $router;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Security\Authenticator
	 */
	public static function createServiceAuthenticator(Container $container)
	{
		return new Kdyby\Security\Authenticator($container->users);
	}



	/**
	 * @param Nette\DI\Container $container
	 * @return Kdyby\Http\User
	 */
	public static function createServiceUser(Nette\DI\Container $container)
	{
		$context = new Container;
		// copies services from $container and preserves lazy loading
		$context->lazyCopy('authenticator', $container);
		$context->lazyCopy('authorizator', $container);
		$context->lazyCopy('sqldb', $container);
		$context->addService('session', $container->session);

		return new Kdyby\Security\User($context);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Security\Users
	 */
	public static function createServiceUsers(Container $container)
	{
		return new Kdyby\Security\Users($container->sqldb->entityManager);
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
	 * @return Kdyby\Modules\InstallWizard
	 */
	public static function createServiceInstallWizard(Container $container)
	{
		return new Kdyby\Modules\InstallWizard($container->robotLoader, $container->cacheStorage);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Cache
	 */
	public static function createServiceDoctrineCache(Container $container)
	{
		return new Kdyby\Doctrine\Cache($container->cacheStorage);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Mapping\TypeMapper
	 */
	public static function createServiceDoctrineTypeMapper(Container $container)
	{
		return new Kdyby\Doctrine\Mapping\TypeMapper();
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Doctrine\Mapping\EntityValuesMapper
	 */
	public static function createServiceDoctrineEntityValuesMapper(Container $container)
	{
		return new Kdyby\Doctrine\Mapping\EntityValuesMapper($container->workspace, $container->doctrineTypeMapper);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Forms\Mapping\EntityFormMapperFactory
	 */
	public static function createServiceEntityFormMapperFactory(Container $container)
	{
		return new Kdyby\Forms\Mapping\EntityFormMapperFactory($container->workspace, $container->doctrineTypeMapper);
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Forms\EntityFormFactory
	 */
	public static function createServiceEntityFormFactory(Container $container)
	{
		return new Kdyby\Forms\EntityFormFactory($container->entityFormMapperFactory);
	}



	/**
	 * @param Container $container
	 * @return ModuleCascadeRegistry
	 */
	public static function createServiceModuleRegistry(Container $container)
	{
		$register = new ModuleCascadeRegistry;

		foreach ($container->getParam('modules', array()) as $namespace => $path) {
			$register->add($namespace, $container->expand($path));
		}

		return $register;
	}



	/**
	 * @param Container $container
	 * @return Console\Helper\HelperSet
	 */
	public static function createServiceConsoleHelpers(Container $container)
	{
		$helperSet = new Console\Helper\HelperSet(array(
			'container' => new ContainerHelper($container),
			'em' => new Kdyby\Doctrine\ORM\EntityManagerHelper($container->sqldb),
			'couchdb' => new Kdyby\Doctrine\ODM\CouchDBHelper($container->couchdb),
		));

		return $helperSet;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Tools\FreezableArray
	 */
	public static function createServiceConsoleCommands(Container $container)
	{
		return new Kdyby\Tools\FreezableArray(array(
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

			// ODM
			new CouchDBCommand\ReplicationStartCommand(),
			new CouchDBCommand\ReplicationCancelCommand(),
			new CouchDBCommand\ViewCleanupCommand(),
			new CouchDBCommand\CompactDatabaseCommand(),
			new CouchDBCommand\CompactViewCommand(),
			new CouchDBCommand\MigrationCommand(),
			new OdmCommand\UpdateDesignDocCommand()
		));
	}



	/**
	 * @param Container $container
	 * @return Console\Application
	 */
	public static function createServiceConsole(Container $container)
	{
		$name = Kdyby\Framework::NAME . " Command Line Interface";
		$cli = new Console\Application($name, Kdyby\Framework::VERSION);

		$cli->setCatchExceptions(TRUE);
		$cli->setHelperSet($container->consoleHelpers);
		$cli->addCommands($container->consoleCommands->freeze()->iterator->getArrayCopy());

		return $cli;
	}



	/**
	 * @param Container $container
	 * @return Kdyby\Components\Grinder\GridFactory
	 */
	public static function createServiceGrinderFactory(Container $container)
	{
		return new Kdyby\Components\Grinder\GridFactory($container->workspace, $container->session);
	}

}