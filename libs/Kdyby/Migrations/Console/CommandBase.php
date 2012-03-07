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
abstract class CommandBase extends Symfony\Component\Console\Command\Command
{

	/** @var \Kdyby\Packages\PackageManager */
	protected $packageManager;

	/** @var \Kdyby\Migrations\MigrationsManager */
	protected $migrationsManager;

	/** @var \Kdyby\Packages\Package */
	protected $package;



	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 */
	protected function initialize(InputInterface $input, OutputInterface $output)
	{
		/** @var \Kdyby\Console\PackageManagerHelper $pmh */
		$pmh = $this->getHelper('packageManager');
		$this->packageManager = $pmh->getPackageManager();

		/** @var \Kdyby\Migrations\Console\MigrationsManagerHelper $mmh */
		$mmh = $this->getHelper('migrationsManager');
		$this->migrationsManager = $mmh->getMigrationsManager();

		// find package
		if ($package = $input->getArgument('package')) {
			try {
				$this->package = $this->packageManager->getPackage($package);

			} catch (\Exception $e) {
				$list = array();
				foreach ($this->packageManager->getPackages() as $package) {
					$list[] = $package->getName();
				}

				throw $e;
			}
		}
	}

}
