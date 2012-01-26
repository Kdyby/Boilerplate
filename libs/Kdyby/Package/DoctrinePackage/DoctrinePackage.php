<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\DoctrinePackage;

use Kdyby;
use Nette;
use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Symfony\Component\Console\Application as ConsoleApp;
use Doctrine\DBAL\Tools\Console\Command as DbalCommand;
use Doctrine\ORM\Tools\Console\Command as OrmCommand;
use Doctrine\DBAL\Migrations\Tools\Console\Command as MigrationCommand;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DoctrinePackage extends Kdyby\Packages\Package
{

	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 */
	public function compile(Nette\Config\Configurator $config, Nette\Config\Compiler $compiler)
	{
		$compiler->addExtension('annotation', new DI\AnnotationExtension());
		$compiler->addExtension('dbal', new DI\DbalExtension());
		$compiler->addExtension('orm', new DI\OrmExtension());
		$compiler->addExtension('migration', new DI\MigrationExtension());
		$compiler->addExtension('fixture', new DI\FixtureExtension());
		$compiler->addExtension('doctrine', new DI\DoctrineExtension());
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
