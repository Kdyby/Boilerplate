<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations;

use Doctrine\DBAL\Connection;
use Kdyby;
use Nette;
use Nette\Utils\Arrays;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Version extends Nette\Object
{

	/** @var \Kdyby\Migrations\History */
	private $history;

	/** @var int */
	private $version;

	/** @var int */
	private $time = 0;

	/** @var string */
	private $class;

	/** @var array */
	private $sql = array();

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $outputWriter;



	/**
	 * @param \Kdyby\Migrations\History $history
	 * @param string $class
	 */
	public function __construct(History $history, $class)
	{
		$this->history = $history;
		$this->class = $class;
		$this->version = (int)\DateTime::createFromFormat('YmdHis', (int)substr($class, -14))->format('YmdHis');
	}



	/**
	 * @return \Kdyby\Migrations\History
	 */
	public function getHistory()
	{
		return $this->history;
	}



	/**
	 * @return int
	 */
	public function getVersion()
	{
		return $this->version;
	}



	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}



	/**
	 * @return bool
	 */
	public function isMigrated()
	{
		return $this->history->getPackage()->getMigrationVersion() >= $this->getVersion();
	}



	/**
	 * @return bool
	 */
	public function isReversible()
	{
		$reflClass = Nette\Reflection\ClassType::from($this->class);
		$declaringClass = $reflClass->getMethod('down')->getDeclaringClass();
		return $reflClass->getName() === $declaringClass->getName();
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param bool $commit
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
	 * @param bool $commit
	 *
	 * @return array
	 */
	public function down(MigrationsManager $manager, $commit = TRUE)
	{
		$this->setOutputWriter($manager->getOutputWriter());
		return $this->execute($manager->getConnection(), 'down', $commit);
	}



	/**
	 * @param \Kdyby\Migrations\MigrationsManager $manager
	 * @param bool $up
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
	 * @param bool $commit
	 *
	 * @return array
	 */
	protected function execute(Connection $connection, $direction, $commit = TRUE)
	{
		$this->sql = array();

		$migration = $this->createMigration();
		$migration->setConnection($connection);

		$sm = $connection->getSchemaManager();
		$platform = $connection->getDatabasePlatform();

		$connection->beginTransaction();

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
				$this->message('<comment>-></comment> ' . $query);

				if ($commit) {
					$connection->executeQuery($query, $params, $types);
				}
			}

			// after migration
			$migration->{'post' . ucfirst($direction)}($toSchema);
			$this->markMigrated($commit);
			$this->time = microtime(TRUE) - $start;

			$time = number_format($this->time * 1000, 1, '.', ' ');
			if ($direction === 'up') {
				$this->message('<info>++</info> migrated <comment>' . $this->getVersion() . '</comment> in ' . $time . ' ms');

			} else {
				$this->message('<info>--</info> reverted <comment>' . $this->getVersion() . '</comment> in ' . $time . ' ms');
			}

			$connection->commit();
			return $this->sql;

		} catch (SkipException $e) {
			$connection->rollback();
			$this->markMigrated($commit);

			$this->message('<info>SS</info> migration <comment>' . $this->version . '</comment> skipped, reason: ' . $e->getMessage());
			return array();

		} catch (\Exception $e) {
			$this->message('<error>Migration ' . $this->version . ' failed. ' . $e->getMessage() . '</error>');
			$connection->rollback();
			throw $e;
		}
	}



	/**
	 * @return \Kdyby\Migrations\AbstractMigration
	 */
	protected function createMigration()
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
	 * @param bool $commit
	 */
	public function markMigrated($commit = TRUE)
	{
		if ($commit) {
			$this->getHistory()->setCurrent($this);
		}
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
		return $this->nth(1);
	}



	/**
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	public function getPrevious()
	{
		return $this->nth(-1);
	}



	/**
	 * @param int $nth
	 *
	 * @return \Kdyby\Migrations\Version|NULL
	 */
	private function nth($nth)
	{
		$versions = $this->history->toArray();
		$currentOffset = Arrays::searchKey($versions, $this->version);
		if (($offset = $currentOffset + $nth) >= 0) {
			return current(array_slice($versions, $offset, 1)) ?: NULL;
		}
		return NULL;
	}

}
