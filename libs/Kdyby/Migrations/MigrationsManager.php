<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Doctrine;
use Doctrine\Common\EventManager;
use Kdyby;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Kdyby\Doctrine\Registry;
use Kdyby\Packages\PackageManager;
use Nette;
use Symfony\Component\Console\Output;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MigrationsManager extends Nette\Object
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Packages\PackageManager */
	private $packageManager;

	/** @var \Doctrine\DBAL\Connection */
	private $connection;

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $outputWriter;

	/**
	 * @var bool
	 */
	private $schemaOk = FALSE;



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 * @param \Kdyby\Packages\PackageManager $packageManager
	 */
	public function __construct(Registry $doctrine, PackageManager $packageManager)
	{
		$this->entityManager = $doctrine->getEntityManager();
		$this->packageManager = $packageManager;
		$this->connection = $this->entityManager->getConnection();
	}



	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface $writer
	 */
	public function setOutputWriter(Output\OutputInterface $writer)
	{
		$this->outputWriter = $writer;
	}



	/**
	 * @return \Symfony\Component\Console\Output\OutputInterface
	 */
	public function getOutputWriter()
	{
		if ($this->outputWriter === NULL) {
			$this->outputWriter = new Output\ConsoleOutput();
		}

		return $this->outputWriter;
	}



	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * @param string $entityClass
	 */
	private function ensureSchema($entityClass)
	{
		/** @var \Kdyby\Doctrine\Mapping\ClassMetadata $class */
		$class = $this->entityManager->getClassMetadata($entityClass);
		if ($this->connection->getSchemaManager()->tablesExist($class->getTableName())) {
			return;
		}

		$schemaTool = new Doctrine\ORM\Tools\SchemaTool($this->entityManager);
		foreach ($schemaTool->getCreateSchemaSql(array($class)) as $sql) {
			$this->connection->executeQuery($sql);
		}
	}



	/**
	 * @return \Kdyby\Doctrine\Dao
	 */
	protected function getPackages()
	{
		if (!$this->schemaOk) {
			$this->ensureSchema('Kdyby\Migrations\PackageVersion');
			$this->ensureSchema('Kdyby\Migrations\MigrationLog');
			$this->schemaOk = TRUE;
		}

		return $this->entityManager->getRepository('Kdyby\Migrations\PackageVersion');
	}



	/**
	 * @param string $packageName
	 * @return \Kdyby\Migrations\PackageVersion
	 */
	public function getPackageVersion($packageName)
	{
		$package = $this->getPackages()->findOneBy(array('name' => $packageName));
		if (!$package) {
			$package = new PackageVersion($this->packageManager->getPackage($packageName));
			$this->getPackages()->save($package);
		}
		return $package;
	}



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 */
	public function savePackage(PackageVersion $package)
	{
		$this->getPackages()->save($package);
	}



	/**
	 * @param string $packageName
	 *
	 * @return \Kdyby\Migrations\History
	 */
	public function getPackageHistory($packageName)
	{
		$history = $this->getPackageVersion($packageName)->createHistory();
		$package = $this->packageManager->getPackage($packageName);
		foreach ($package->getMigrations() as $migration) {
			$history->add($migration);
		}
		return $history;
	}



	/**
	 * @param string $packageName
	 *
	 * @return \Kdyby\Migrations\History
	 */
	public function install($packageName)
	{
		$history = $this->getPackageHistory($packageName);
		$history->migrate($this, date('YmdHis'));

		return $history;
	}



	/**
	 * @param string $packageName
	 *
	 * @return \Kdyby\Migrations\History
	 */
	public function uninstall($packageName)
	{
		$history = $this->getPackageHistory($packageName);
		$history->migrate($this, 0);

		return $history;
	}

}
