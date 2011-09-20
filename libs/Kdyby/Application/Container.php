<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Doctrine\DBAL\Tools\Console\Command as DbalCommand;
use Doctrine\ORM\Tools\Console\Command as OrmCommand;
use Doctrine\CouchDB\Tools\Console\Command as CouchDBCommand;
use Doctrine\ODM\CouchDB\Tools\Console\Command as OdmCommand;
use Kdyby;
use Kdyby\DI\ContainerHelper;
use Nette;
use Symfony\Component\Console;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\Doctrine\Workspace $workspace
 * @property-read Kdyby\Doctrine\Cache $doctrineCache
 * @property-read Kdyby\Doctrine\ORM\Container $sqldb
 * @property-read Kdyby\Doctrine\ORM\ContainerBuilder $sqldbContainerBuilder
 * @property-read Kdyby\Doctrine\ODM\Container $couchdb
 *
 * @property-read Console\Helper\HelperSet $consoleHelpers
 * @property-read Kdyby\Tools\FreezableArray $consoleCommands
 * @property-read Console\Application $console
 *
 * @property-read Nette\Application\Application $application
 * @property-read Nette\Application\IPresenterFactory $presenterFactory
 * @property-read Kdyby\Application\ModuleCascadeRegistry $moduleRegistry
 * @property-read Kdyby\Application\RequestManager $requestManager
 * @property-read Kdyby\Config\Settings $settings
 *
 * @property-read Nette\Application\IRouter $router
 * @property-read Nette\Http\Request $httpRequest
 * @property-read Nette\Http\Response $httpResponse
 * @property-read Nette\Http\Context $httpContext
 * @property-read Nette\Http\Session $session
 *
 * @property-read Nette\Http\User $user
 * @property-read Kdyby\Security\Users $users
 *
 * @property-read Kdyby\Templates\ITemplateFactory $templateFactory
 * @property-read Nette\Caching\Storages\PhpFileStorage $templateCacheStorage
 * @property-read Nette\Latte\Engine $latteEngine
 *
 * @property-read Nette\Loaders\RobotLoader $robotLoader
 *
 * @property-read Nette\Caching\IStorage $cacheStorage
 * @property-read Nette\Caching\Storages\IJournal $cacheJournal
 *
 * @property-read Nette\Mail\IMailer $mailer
 *
 * @property-read Kdyby\Modules\InstallWizard $installWizard
 */
class Container extends Kdyby\DI\Container
{

	/**
	 * @return Kdyby\Application\RequestManager
	 */
	protected function createServiceRequestManager()
	{
		return new Kdyby\Application\RequestManager($this->application, $this->session);
	}



	/**
	 * @return Kdyby\Modules\InstallWizard
	 */
	protected function createServiceInstallWizard()
	{
		return new Kdyby\Modules\InstallWizard($this->robotLoader, $this->cacheStorage);
	}



	/**
	 * @return Kdyby\Doctrine\Cache
	 */
	protected function createServiceDoctrineCache()
	{
		return new Kdyby\Doctrine\Cache($this->cacheStorage);
	}



	/**
	 * @return Kdyby\Doctrine\Workspace
	 */
	protected function createServiceWorkspace()
	{
		$containers = array(
			'sqldb' => $this->sqldb,
			'couchdb' => $this->couchdb
		);

		$containers += $this->getServiceNamesByTag('database');
		return new Kdyby\Doctrine\Workspace($containers);
	}



	/**
	 * @return ModuleCascadeRegistry
	 */
	protected function createServiceModuleRegistry()
	{
		$register = new ModuleCascadeRegistry;

		foreach ($this->getParam('modules', array()) as $namespace => $path) {
			$register->add($namespace, $this->expand($path));
		}

		return $register;
	}



	/**
	 * @return Console\Helper\HelperSet
	 */
	protected function createServiceConsoleHelpers()
	{
		$helperSet = new Console\Helper\HelperSet(array(
			'container' => new ContainerHelper($this),
			'em' => new Kdyby\Doctrine\ORM\EntityManagerHelper($this->sqldb),
			'couchdb' => new Kdyby\Doctrine\ODM\CouchDBHelper($this->couchdb),
		));

		return $helperSet;
	}



	/**
	 * @return Kdyby\Tools\FreezableArray
	 */
	protected function createServiceConsoleCommands()
	{
		$commands = array();

		if ($this->hasService('sqldb')) {
			// DBAL Commands
			$commands[] = new DbalCommand\RunSqlCommand();
			$commands[] = new DbalCommand\ImportCommand();

			// ORM Commands
			$commands[] = new OrmCommand\SchemaTool\CreateCommand();
			$commands[] = new OrmCommand\SchemaTool\UpdateCommand();
			$commands[] = new OrmCommand\SchemaTool\DropCommand();
			$commands[] = new OrmCommand\ValidateSchemaCommand();
			$commands[] = new OrmCommand\GenerateProxiesCommand();
			$commands[] = new OrmCommand\RunDqlCommand();
		}

		if ($this->hasService('couchdb')) {
			// ODM
			$commands[] = new CouchDBCommand\ReplicationStartCommand();
			$commands[] = new CouchDBCommand\ReplicationCancelCommand();
			$commands[] = new CouchDBCommand\ViewCleanupCommand();
			$commands[] = new CouchDBCommand\CompactDatabaseCommand();
			$commands[] = new CouchDBCommand\CompactViewCommand();
			$commands[] = new CouchDBCommand\MigrationCommand();
			$commands[] = new OdmCommand\UpdateDesignDocCommand();
		}

		return new Kdyby\Tools\FreezableArray($commands);
	}



	/**
	 * @return Console\Application
	 */
	protected function createServiceConsole()
	{
		$name = Kdyby\Framework::NAME . " Command Line Interface";
		$cli = new Console\Application($name, Kdyby\Framework::VERSION);

		$cli->setCatchExceptions(TRUE);
		$cli->setHelperSet($this->consoleHelpers);
		$cli->addCommands($this->consoleCommands->freeze()->iterator->getArrayCopy());

		return $cli;
	}



	/**
	 * @return Kdyby\Security\Users
	 */
	protected function createServiceUsers()
	{
		return new Kdyby\Security\Users($this->sqldb->entityManager);
	}



	/**
	 * @return Kdyby\Components\Grinder\GridFactory
	 */
	protected function createServiceGrinderFactory()
	{
		return new Kdyby\Components\Grinder\GridFactory($this->workspace, $this->session);
	}

}