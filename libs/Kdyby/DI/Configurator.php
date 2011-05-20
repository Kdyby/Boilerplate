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
use Kdyby;
use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\UI\Presenter;
use Nette\DI;
use Symfony\Component\Console;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 */
class Configurator extends Nette\Configurator
{

	/**
	 * @param string $containerClass
	 */
	public function __construct($containerClass = 'Kdyby\DI\Container')
	{
		parent::__construct($containerClass);
	}



	/**
	 * @param DI\Container $container
	 * @param array $options
	 * @return Kdyby\Application\Application
	 */
	public static function createServiceApplication(DI\Container $container, array $options = NULL)
	{
		$context = new Kdyby\DI\Container;
		$context->addService('httpRequest', $container->httpRequest);
		$context->addService('httpResponse', $container->httpResponse);
		$context->addService('session', $container->session);
		$context->addService('presenterFactory', $container->presenterFactory);
		$context->addService('router', 'Nette\Application\Routers\RouteList');
		$context->addService('console', $container->console);

		Presenter::$invalidLinkMode = $container->getParam('productionMode')
			? Presenter::INVALID_LINK_SILENT : Presenter::INVALID_LINK_WARNING;

		$application = new Kdyby\Application\Application($context);
		$application->catchExceptions = $container->getParam('productionMode');
		$application->errorPresenter = 'Error';

		return $application;
	}



	/**
	 * @param DI\Container $container
	 * @return Kdyby\Doctrine\Container
	 */
	public static function createServiceDoctrine(DI\Container $container)
	{
		$doctrine = new Kdyby\Doctrine\Container;
		$doctrine->addService('container', $container);

		return $doctrine;
	}



	/**
	 * @param DI\Container $container
	 * @return Nette\Application\Routers\RouteList
	 */
	public static function createServiceRouter(DI\Container $container)
	{
		$router = new Nette\Application\Routers\RouteList;

		$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}



	/**
	 * @param DI\IContainer $container
	 * @return Console\Helper\HelperSet
	 */
	public static function createServiceConsoleHelpers(DI\IContainer $container)
	{
		$helperSet = new Console\Helper\HelperSet;
		$helperSet->set(new ContainerHelper($container), 'container');
		$helperSet->set(new Kdyby\Doctrine\EntityManagerHelper($container), 'em');

		return $helperSet;
	}



	/**
	 * @param DI\IContainer $container
	 * @return Kdyby\Tools\FreezableArray
	 */
	public static function createServiceConsoleCommands(DI\IContainer $container)
	{
		$commands = new Kdyby\Tools\FreezableArray(array(
			// DBAL Commands
			new DbalCommand\RunSqlCommand(),
			new DbalCommand\ImportCommand(),

			// ORM Commands
			new OrmCommand\SchemaTool\CreateCommand(),
			new OrmCommand\SchemaTool\UpdateCommand(),
			new OrmCommand\SchemaTool\DropCommand(),
			new OrmCommand\GenerateProxiesCommand(),
			new OrmCommand\RunDqlCommand(),
		));

		return $commands->freeze();
	}



	/**
	 * @param DI\IContainer $container
	 * @return Console\Application
	 */
	public static function createServiceConsole(DI\Container $container)
	{
		$name = Kdyby\Framework::NAME . " Command Line Interface";
		$cli = new Console\Application($name, Kdyby\Framework::VERSION);

		$cli->setCatchExceptions(TRUE);
		$cli->setHelperSet($container->consoleHelpers);
		$cli->addCommands($container->consoleCommands->iterator->getArrayCopy());

		return $cli;
	}

}