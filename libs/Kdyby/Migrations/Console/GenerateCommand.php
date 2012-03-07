<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations\Console;

use Kdyby;
use Nette;
use Symfony;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;



/**
 * Command for generating new migration classes
 *
 * @todo: automatically open in IDE
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class GenerateCommand extends CommandBase
{

	/**
	 */
	protected function configure()
	{
        $this
			->setName('kdyby:generate:migration')
			->setDescription('Generate a migration class.')
			->addArgument('package', InputArgument::REQUIRED, "Name of the package, that will be command working with.")
			->addArgument('entity', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, "List of entities, that will be command working with.")
			->addOption('--sql', NULL, InputOption::VALUE_NONE, "Instead of creating migration class, dump everything to sql file. This kind of migration is automatically irreversible.")
			->addOption('--dump-rows', '-r', InputOption::VALUE_NONE, "For creating INSERT commands for rows in table of the given entity.")
			->addOption('--append', '-a', InputOption::VALUE_NONE, "Instead of creating new file, migration will be appended to the latest one.")
			->setHelp(<<<HELP
The <info>%command.name%</info> command generates a migration class by comparing your current database to your mapping information:
    <info>%command.full_name% MyPackageName</info>

When one or more <comment>entities</comment> are specified, the <info>%command.name%</info> command will take them into consideration,
and all options will work not with all entities, but only with the specified ones:
    <info>%command.full_name% MyPackageName Article Tag Comment</info>

By specifying the <comment>--sql</comment> option, the migration will be dumped to <comment>.sql</comment> file, instead of <comment>migration class</comment>
    <info>%command.full_name% --sql MyPackageName</info>

The <comment>--dump-rows</comment> option will make command write the rows of specified entities (or all entities of specified package) to generated migration:
    <info>%command.full_name% -r MyPackageName</info>
    <info>%command.full_name% -r MyPackageName Article Tag Comment</info>

The <comment>--append</comment> option will make the command append your migration to latest created migration, no matter if <comment>migration class</comment> or <comment>.sql</comment> file.
    <info>%command.full_name% -a MyPackageName</info>
HELP
			);
	}



	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		dump($this);
	}

}
