<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations\Console;

use Kdyby;
use Kdyby\Migrations\Tools\PackageMigration;
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
			->addOption('force', NULL, InputArgument::REQUIRED, "Migration won't start, unless you force it.")
			->setHelp(<<<HELP
The <info>%command.name%</info> command migrates all packages or the given one:
    <info>%command.full_name% MyPackageName</info>

By specifying the <comment>version</comment>, the command migrates to the specified timestamp. When given only date, it migrates to the end of day.
    <info>%command.full_name% MyPackageName Y-m-d H:i:s</info>
    <info>%command.full_name% MyPackageName Y-m-d</info>

You can also migrate by one step only
    <info>%command.full_name% MyPackageName up</info>
    <info>%command.full_name% MyPackageName down</info>
HELP
		);
	}



	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$targetVersion = $this->package ? $input->getArgument('version') : $input->getArgument('package');
		if ($targetVersion === "0") {
			$targetVersion = 0;

		} elseif ($targetVersion === NULL) {
			$targetVersion = date('YmdHis');
		}

		$force = $input->getOption('force');
		if ($this->package) {
			try {
				$migration = new PackageMigration($this->migrationsManager, $this->package);
				$migration->run($targetVersion, $force);

			} catch (Kdyby\Migrations\MigrationException $e) {
				$output->writeln("");
				$output->writeln('    ' . $e->getMessage());
			}

		} else {
			foreach ($this->packageManager->getPackages() as $package) {
				try {
					$migration = new PackageMigration($this->migrationsManager, $package);
					$migration->run($targetVersion, $force);

				} catch (Kdyby\Migrations\MigrationException $e) {
					$output->writeln("");
					$output->writeln('    ' . $e->getMessage());
				}
			}
		}

		if (!$force) {
			$output->writeln('');
			$output->writeln("If everything looks fine, add the --force option.");
		}
	}

}
