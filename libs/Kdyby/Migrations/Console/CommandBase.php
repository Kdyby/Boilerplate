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
use Nette;
use Nette\Utils\Strings;
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

	/** @var \Doctrine\ORM\EntityManager */
	protected $entityManager;

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
		$this->migrationsManager->setOutputWriter($output);

		/** @var \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper $emh */
		$emh = $this->getHelper('entityManager');
		$this->entityManager = $emh->getEntityManager();

		// find package
		if ($package = $input->getArgument('package')) {
			if (!Strings::match($package, '~^[a-z][a-z0-9]*$~i')) {
				return;
			}

			try {
				$this->package = $this->packageManager->getPackage($package);
			} catch (\Exception $e) { }
		}
	}

}
