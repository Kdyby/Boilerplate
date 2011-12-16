<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages\DoctrinePackage;

use Kdyby;
use Nette;
use Symfony\Component\Console\Application as ConsoleApp;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Doctrine\DBAL\Tools\Console\Command as DbalCommand;
use Doctrine\ORM\Tools\Console\Command as OrmCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command as MigrationCommand;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DoctrinePackage extends Kdyby\Package\Package
{

	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param ContainerBuilder $container
	 */
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new DI\Compiler\RegisterEventListenersAndSubscribersPass());
	}



	/**
	 * Finds and registers Commands.
	 *
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(ConsoleApp $app)
	{
		parent::registerCommands($app);

		$app->addCommands(array(
			// DBAL Commands
			new DbalCommand\RunSqlCommand(),
			new DbalCommand\ImportCommand(),

			// ORM Commands
			//new OrmCommand\ClearCache\MetadataCommand(),
			//new OrmCommand\ClearCache\ResultCommand(),
			//new OrmCommand\ClearCache\QueryCommand(),
			new OrmCommand\SchemaTool\CreateCommand(),
			new OrmCommand\SchemaTool\UpdateCommand(),
			new OrmCommand\SchemaTool\DropCommand(),
			//new OrmCommand\EnsureProductionSettingsCommand(),
			//new OrmCommand\ConvertDoctrine1SchemaCommand(),
			//new OrmCommand\GenerateRepositoriesCommand(),
			//new OrmCommand\GenerateEntitiesCommand(),
			new OrmCommand\GenerateProxiesCommand(),
			new OrmCommand\ConvertMappingCommand(),
			new OrmCommand\RunDqlCommand(),
			new OrmCommand\ValidateSchemaCommand(),
			new OrmCommand\InfoCommand(),

			// Migrations Commands
			new MigrationCommand\ExecuteCommand(),
			new MigrationCommand\GenerateCommand(),
			new MigrationCommand\MigrateCommand(),
			new MigrationCommand\StatusCommand(),
			new MigrationCommand\VersionCommand()
		));
	}

}
