<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations\Tools;

use Kdyby;
use Kdyby\Migrations\MigrationException;
use Kdyby\Migrations\MigrationsManager;
use Kdyby\Packages\Package;
use Kdyby\Tools\DateTime;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackageMigration extends Nette\Object
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
	 * @var \Kdyby\Migrations\MigrationsManager
	 */
	private $migrationsManager;

	/**
	 * @var \Kdyby\Packages\Package
	 */
	private $package;

	/**
	 * @var \Kdyby\Migrations\History
	 */
	private $history;



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $migrationsManager
	 * @param \Kdyby\Packages\Package $package
	 */
	public function __construct(MigrationsManager $migrationsManager, Package $package)
	{
		$this->migrationsManager = $migrationsManager;
		$this->package = $package;
		$this->history = $migrationsManager->getPackageHistory($package->getName());
	}



	/**
	 * @param $targetVersion
	 * @param bool $force
	 *
	 * @throws \Kdyby\Migrations\MigrationException
	 */
	public function run($targetVersion, $force = FALSE)
	{
		$packageName = $this->package->getName();

		if (in_array($targetVersion, array('up', 'apply'), TRUE)) {
			if ($nextVersion = $this->history->getNext()) {
				$targetVersion = $nextVersion->getVersion();

			} else {
				throw new MigrationException("Next version for <comment>$packageName</comment> not found");
			}

		} elseif (in_array($targetVersion, array('down', 'revert'), TRUE)) {
			if ($this->history->getCurrent()) {
				$targetVersion = ($prevVersion = $this->history->getPrevious()) ? $prevVersion->getVersion() : 0;

			} else {
				$targetVersion = 0;
			}

		} elseif (strlen((string)$targetVersion) > 10 && ($date = DateTime::tryFormats(static::$formats, $targetVersion))) {
			$targetVersion = $date->format('YmdHis');
		}

		if (!$this->history->getFirst()) { // no migrations
			if ($targetVersion !== 0) {
				$schema = new CreatePackageSchema($this->migrationsManager->getEntityManager(), $this->package);
				$schema->setOutputWriter($this->migrationsManager->getOutputWriter());
				$schema->create($force);

			} else {
				$schema = new DropPackageSchema($this->migrationsManager->getEntityManager(), $this->package);
				$schema->setOutputWriter($this->migrationsManager->getOutputWriter());
				$schema->create($force);
			}

			return;
		}

		if ($this->isUpToDate($targetVersion)) {
			throw new MigrationException("Package <comment>$packageName</comment> is up to date.");
		}

		$this->history->migrate($this->migrationsManager, $targetVersion, $force);
	}



	/**
	 * @param string $targetVersion
	 * @return bool
	 */
	private function isUpToDate($targetVersion)
	{
		return ($this->history->isUpToDate()
			&& (!($curr = $this->history->getCurrent()) || $targetVersion >= $curr->getVersion()))
			|| $this->history->getPackage()->getMigrationVersion() === $targetVersion;
	}

}
