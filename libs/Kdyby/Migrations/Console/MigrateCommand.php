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
	 * @var array
	 */
	private static $formats = array(
		'YmdHis',
		'Y-m-d H:i:s',
		'Y-m-d H:i',
		'Y-m-d H',
		'Y-m-d',
	);

	/**
	 * @var \Symfony\Component\Console\Output\OutputInterface
	 */
	private $output;



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
		$this->output = $output;

		$targetVersion = $input->getArgument('version');
		if ($targetVersion === "0") {
			$targetVersion = 0;

		} elseif ($targetVersion === NULL) {
			$targetVersion = date('YmdHis');
		}

		$force = $input->getOption('force');
		if ($this->package) {
			$this->migratePackage($this->package, $targetVersion, $force);

		} else {
			foreach ($this->packageManager->getPackages() as $package) {
				$this->migratePackage($package, $targetVersion, $force);
			}
		}

		if (!$force) {
			$output->writeln('');
			$output->writeln("If everything looks fine, add the --force option.");
		}
	}



	/**
	 * @param \Kdyby\Packages\Package $package
	 * @param string $targetVersion
	 * @param bool $force
	 */
	private function migratePackage(Kdyby\Packages\Package $package, $targetVersion, $force = FALSE)
	{
		$packageName = $package->getName();
		$history = $this->migrationsManager->getPackageHistory($packageName);

		if ($targetVersion === 'up') {
			if ($nextVersion = $history->getNext()) {
				$targetVersion = $nextVersion->getVersion();

			} else {
				$this->output->writeln("Next version for <info>$packageName</info> not found");
				return;
			}

		} elseif ($targetVersion === 'down') {
			if ($history->getCurrent()) {
				$targetVersion = ($prevVersion = $history->getPrevious()) ? $prevVersion->getVersion() : 0;

			} else {
				$this->output->writeln("Previous version for <info>$packageName</info> not found");
				return;
			}

		} else {
			if ($date = Kdyby\Tools\DateTime::tryFormats(static::$formats, $targetVersion)) {
				$targetVersion = $date->format('YmdHis');
			}
		}

		if ($history->isUpToDate() && (!($curr = $history->getCurrent()) || $targetVersion >= $curr->getVersion())) {
			$this->output->writeln("Package <info>$packageName</info> is up to date.");
			return;
		}

		$history->migrate($this->migrationsManager, $targetVersion, $force);

	}

}
