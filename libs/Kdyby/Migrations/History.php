<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations;

use Kdyby;
use Kdyby\Tools\Arrays;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class History extends Nette\Object implements \IteratorAggregate
{

	/** @var \Kdyby\Migrations\Version[] */
	private $versions = array();

	/** @var int */
	private $current;

	/** @var \Kdyby\Migrations\PackageVersion */
	private $package;



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 * @param $current
	 */
	public function __construct(PackageVersion $package, $current)
	{
		$this->package = $package;
		$this->current = (int)$current;
	}



	/**
	 * @return \Kdyby\Migrations\PackageVersion
	 */
	public function getPackage()
	{
		return $this->package;
	}



	/**
	 * @param \Kdyby\Migrations\Version|NULL $version
	 */
	public function setCurrent(Version $version = NULL)
	{
		$this->package->setVersion($version);
		$this->current = $version ? (int)$this->package->getMigrationVersion() : 0;
	}



	/**
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	public function getCurrent()
	{
		if ($this->current && isset($this->versions[$this->current])) {
			return $this->versions[$this->current];
		}
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param int $target
	 * @param boolean $commit
	 *
	 * @throws \Kdyby\Migrations\MigrationException
	 * @return array
	 */
	public function migrate(MigrationsManager $manager, $target, $commit = TRUE)
	{
		$sqls = array();
		$writer = $manager->getOutputWriter();

		// without registered migrations, there is no job to be done
		if (!$this->versions) {
			return $sqls;
		}

		try {
			$version = $this->getCurrent();
			$packageName = $this->package->getName();
			if ($up = $this->current < ($target = $this->calculateClosestVersion($target))) {
				if (!$version) {
					$writer->writeln('    Migrating <comment>' . $packageName . '</comment> to <comment>' . $target . '</comment>');
					$version = reset($this->versions);

				} else {
					$writer->writeln('    Migrating <comment>' . $packageName . '</comment> to <comment>' . $target . '</comment> from <comment>' . $version->getVersion() . '</comment>');
				}

			} else {
				$writer->writeln('    Reverting <comment>' . $packageName . '</comment> to <comment>' . $target . '</comment> from <comment>' . $version->getVersion() . '</comment>');
			}

			$totalTime = $totalSqls = 0;
			do {
				$sqls[$version->getVersion()] = $lastSqls = $up
					? $version->up($manager, $commit)
					: $version->down($manager, $commit);

				$totalTime += $version->getTime();
				$totalSqls += is_array($lastSqls) ? count($lastSqls) : (int)$lastSqls;

				if (!$up && $version === $this->getFirst()) {
					$this->setCurrent(NULL);
				}

				if (($up && $version->getVersion() >= $target) || (!$up && $version->getVersion() <= $target)) {
					break; // end the migration when the target is achieved
				}

			} while ($version = ($up ? $version->getNext() : $version->getPrevious()));

			$writer->writeln(NULL);
			$writer->writeln('    <comment>------------------------</comment>');
			$writer->writeln('    <info>++</info> finished in ' . number_format($totalTime, 2, '.', ' ') . ' s');
			$writer->writeln('    <info>++</info> ' . count($sqls) . ' migrations executed');
			$writer->writeln('    <info>++</info> ' . $totalSqls . ' sql queries');

		} catch (\Exception $exception) { }

		if ($commit) {
			$manager->savePackage($this->package);
		}

		if (isset($exception)) {
			throw $exception;
		}

		return $sqls;
	}



	/**
	 * Ensures the target is in range
	 * @param int $target
	 *
	 * @return int
	 */
	private function calculateClosestVersion($target)
	{
		return max($this->getFirst()->getVersion(), min($target, $this->getLast()->getVersion()));
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param int $time
	 *
	 * @return array
	 */
	public function dumpSql(MigrationsManager $manager, $time)
	{
		return $this->migrate($manager, $time, FALSE);
	}



	/**
	 * @return bool
	 */
	public function isUpToDate()
	{
		$last = end($this->versions);
		return !$last || $last->getVersion() === $this->package->getMigrationVersion();
	}



	/**
	 * @param string $migration
	 *
	 * @return \Kdyby\Migrations\Version
	 */
	public function add($migration)
	{
		if (class_exists($migration)) {
			$version = new Version($this, $migration);

		} elseif (is_file($migration) && pathinfo($migration, PATHINFO_EXTENSION) === 'sql') {
			$version = new SqlVersion($this, $migration);

		} else {
			throw new Kdyby\InvalidArgumentException("Given migration is neither migration class or sql dump.");
		}

		if (isset($this->versions[$version->getVersion()])) {
			throw new Kdyby\InvalidStateException('Given version ' . $version->getVersion() . ' is already registered.');
		}

		$this->versions[$version->getVersion()] = $version;
		ksort($this->versions);
		return $version;
	}



	/**
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	public function getFirst()
	{
		return reset($this->versions);
	}



	/**
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	public function getLast()
	{
		return end($this->versions);
	}



	/**
	 * @return \Kdyby\Migrations\Version[]
	 */
	public function toArray()
	{
		return $this->versions;
	}



	/********************** \IteratorAggregate **********************/


	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->versions);
	}

}
