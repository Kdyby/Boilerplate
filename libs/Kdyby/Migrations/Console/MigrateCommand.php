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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MigrateCommand extends CommandBase
{

	/**
	 */
	protected function configure()
	{
        $this
			->setName('kdyby:migrate')
			->setDescription('Migrates database.')
			->addArgument('package', InputArgument::OPTIONAL, "Name of the package, that will be migrated.")
			->addArgument('version', InputArgument::OPTIONAL, "Date to be migrated to.")
			->setHelp(<<<HELP
The <info>%command.name%</info> command migrates all packages or the given one:
    <info>%command.full_name% MyPackageName</info>

By specifying the <comment>version</comment>, the command migrates to the specified timestamp. When given only date, it migrates to the end of day.
    <info>%command.full_name% MyPackageName Y-m-d H:i:s</info>
    <info>%command.full_name% MyPackageName Y-m-d</info>

By specifying the <comment>--sql</comment> option, the migration will be dumped to <comment>.sql</comment> file, instead of <comment>migration class</comment>
    <info>%command.full_name% --sql MyPackageName</info>
HELP
		);
	}



	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{

	}

}
