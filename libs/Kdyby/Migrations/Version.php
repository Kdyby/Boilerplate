<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Doctrine\DBAL\Connection;
use Kdyby;
use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Version extends Nette\Object
{

	/** @var \Kdyby\Migrations\History */
	private $history;

	/** @var VersionDatetime */
	private $version;

	/** @var int */
	protected $time = 0;

	/** @var string */
	private $class;

	/** @var array */
	private $sql = array();

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $outputWriter;



	/**
	 * @param \Kdyby\Migrations\History $history
	 * @param string $class
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function __construct(History $history, $class)
	{
		$this->history = $history;

		if (class_exists($class)) {
			$this->class = $class;
		}

		if ($formatted = Strings::match($class, '~(\d{14})$~')) {
			if (!$this->version = VersionDatetime::from($formatted[0])) {
				throw new \Kdyby\InvalidArgumentException("Given class '$class' is not valid migration version name.");
			}

		} else {
			throw new \Kdyby\InvalidArgumentException("Given class '$class' is not valid migration version name.");
		}
	}



	/**
	 * @return \Kdyby\Migrations\History
	 */
	public function getHistory()
	{
		return $this->history;
	}



	/**
	 * @return VersionDatetime
	 */
	public function getVersion()
	{
		return $this->version ? clone $this->version : NULL;
	}



	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}



	/**
	 * @return boolean
	 */
	public function isMigrated()
	{
		return $this->history->getPackage()
			->getMigrationVersion() >= $this->getVersion();
	}



	/**
	 * @return boolean
	 */
	public function isReversible()
	{
		$reflClass = Nette\Reflection\ClassType::from($this->class);
		$declaringClass = $reflClass->getMethod('down')->getDeclaringClass();
		return $reflClass->getName() === $declaringClass->getName();
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param boolean $commit
	 *
	 * @return array
	 */
	public function up(MigrationsManager $manager, $commit = TRUE)
	{
		$this->setOutputWriter($manager->getOutputWriter());
		return $this->execute($manager->getConnection(), 'up', $commit);
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param boolean $commit
	 *
	 * @throws MigrationException
	 * @return array
	 */
	public function down(MigrationsManager $manager, $commit = TRUE)
	{
		if (!$this->isReversible()) {
			throw new MigrationException('Migration ' . $this->getVersion() . ' is irreversible, it doesn\'t implement down() method.');
		}

		$this->setOutputWriter($manager->getOutputWriter());
		return $this->execute($manager->getConnection(), 'down', $commit);
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param boolean $up
	 *
	 * @return array
	 */
	public function dump(MigrationsManager $manager, $up = TRUE)
	{
		$this->setOutputWriter($manager->getOutputWriter());
		return $this->execute($manager->getConnection(), $up ? 'up' : 'down', FALSE);
	}



	/**
	 * Add some SQL queries to this versions migration
	 *
	 * @param mixed $sql
	 * @param array $params
	 * @param array $types
	 *
	 * @return void
	 */
	public function addSql($sql, array $params = array(), array $types = array())
	{
		$this->sql[] = array($sql, $params, $types);
	}



	/**
	 * @param \Doctrine\DBAL\Connection $connection
	 * @param string $direction
	 * @param boolean $commit
	 *
	 * @return array
	 */
	private function execute(Connection $connection, $direction, $commit = TRUE)
	{
		$this->sql = array();

		$migration = $this->createMigration();
		$migration->setConnection($connection);

		/** @var \Doctrine\DBAL\Schema\AbstractSchemaManager $sm */
		$sm = $connection->getSchemaManager();
		$platform = $connection->getDatabasePlatform();

		$connection->beginTransaction();
		if ($connection->getDriver()->getName() === 'pdo_mysql') {
			$connection->executeQuery("SET foreign_key_checks = 0");
		}

		try {
			$start = microtime(TRUE);

			// before migration
			$fromSchema = $sm->createSchema();
			$migration->{'pre' . ucfirst($direction)}($fromSchema);

			// migration
			$toSchema = clone $fromSchema;
			$migration->$direction($toSchema);
			foreach ($fromSchema->getMigrateToSql($toSchema, $platform) as $sql) {
				$this->addSql($sql);
			}

			if (!$this->sql) {
				$this->message('<error>Migration ' . $this->getVersion() . ' was executed but did not result in any SQL statements.</error>');
			}

			foreach ($this->sql as $sql) {
				list($query, $params, $types) = $sql;
				$this->message('<comment>-></comment> ' . Strings::replace($query, array('~[\n\r\t ]+~' => ' ')));

				if ($commit) {
					$connection->executeQuery($query, $params, $types);
				}
			}

			// after migration
			$migration->{'post' . ucfirst($direction)}($toSchema);
			$this->markMigrated($direction, $commit);
			$this->time = microtime(TRUE) - $start;

			$time = number_format($this->time * 1000, 1, '.', ' ');
			if ($direction === 'up') {
				$this->message('<info>++</info> migrated <comment>' . $this->getVersion() . '</comment> in ' . $time . ' ms');

			} else {
				$this->message('<info>--</info> reverted <comment>' . $this->getVersion() . '</comment> in ' . $time . ' ms');
			}

			if ($connection->getDriver()->getName() === 'pdo_mysql') {
				$connection->executeQuery("SET foreign_key_checks = 1");
			}
			$connection->commit();
			return $this->sql;

		} catch (SkipException $e) {
			$connection->rollback();
			$this->markMigrated($direction, $commit);

			$this->message('<info>SS</info> migration <comment>' . $this->getVersion() . '</comment> skipped, reason: ' . $e->getMessage());
			return array();

		} catch (\Exception $e) {
			$this->message('<error>Migration ' . $this->getVersion() . ' failed. ' . $e->getMessage() . '</error>');

			$connection->rollback();
			throw $e;
		}
	}



	/**
	 * @return \Kdyby\Migrations\AbstractMigration
	 */
	private function createMigration()
	{
		$class = $this->class;
		return new $class($this, $this->outputWriter);
	}



	/**
	 * @return array
	 */
	public function getSql()
	{
		return $this->sql;
	}



	/**
	 * @param string $direction
	 * @param boolean $commit
	 */
	public function markMigrated($direction, $commit = TRUE)
	{
		if (!$commit) {
			return;
		}

		$current = $direction === 'down' ? $this->getPrevious() : $this;
		$this->getHistory()->setCurrent($current ?: NULL);
	}



	/**
	 * @param string $message
	 */
	protected function message($message)
	{
		if ($this->outputWriter) {
			$this->outputWriter->writeln('    ' . $message);
		}
	}



	/**
	 * @param \Symfony\Component\Console\Output\OutputInterface $writer
	 */
	public function setOutputWriter(OutputInterface $writer)
	{
		$this->outputWriter = $writer;
	}



	/**
	 * @return int
	 */
	public function getTime()
	{
		return $this->time;
	}



	/**
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	public function getNext()
	{
		return $this->history->getNextTo($this);
	}



	/**
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	public function getPrevious()
	{
		return $this->history->getPreviousTo($this);
	}

}
