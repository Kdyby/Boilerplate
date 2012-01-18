<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Connection;
use Kdyby;
use Nette;
use Symfony\Component\Console\Output\OutputInterface;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class AbstractMigration extends Nette\Object
{

	/** @var \Kdyby\Migrations\Version */
	private $version;

	/** @var \Doctrine\DBAL\Schema\AbstractSchemaManager */
	protected $schemaManager;

	/** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
	protected $platform;

	/** @var \Symfony\Component\Console\Output\OutputInterface */
	private $outputWriter;



	/**
	 * @param \Kdyby\Migrations\Version $version
	 * @param \Symfony\Component\Console\Output\OutputInterface $writer
	 */
	final public function __construct(Version $version, OutputInterface $writer)
	{
		$this->version = $version;
		$this->outputWriter = $writer;
	}



	/**
	 * @param \Doctrine\DBAL\Connection $connection
	 */
	final public function setConnection(Connection $connection = NULL)
	{
		$this->schemaManager = $connection ? $connection->getSchemaManager() : NULL;
		$this->platform = $connection ? $connection->getDatabasePlatform() : NULL;
	}



	/**
	 * @param string $sql
	 * @param array $params
	 */
	protected function addSql($sql, array $params = array())
	{
		$this->version->addSql($sql, $params);
	}



	/**
	 * @param string $message
	 */
	protected function message($message)
	{
		$this->outputWriter->writeln("    " . $message);
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function preUp(Schema $schema)
	{
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	abstract public function up(Schema $schema);



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function postUp(Schema $schema)
	{
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function preDown(Schema $schema)
	{
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function down(Schema $schema)
	{

	}



	/**
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 */
	public function postDown(Schema $schema)
	{
	}



	/**
	 * Print a warning message if the condition evalutes to TRUE.
	 *
	 * @param bool $condition
	 * @param string $message
	 */
	public function warnIf($condition, $message = '')
	{
		$message = $message ?: 'Unknown Reason';
		if ($condition === TRUE) {
			$this->message('<warning>Warning: ' . $message . '</warning>');
		}
	}



	/**
	 * Abort the migration if the condition evalutes to TRUE.
	 *
	 * @param bool $condition
	 * @param string $message
	 */
	public function abortIf($condition, $message = '')
	{
		$message = $message ?: 'Unknown Reason';
		if ($condition === TRUE) {
			throw new AbortException($message);
		}
	}



	/**
	 * Skip this migration (but not the next ones) if condition evalutes to TRUE.
	 *
	 * @param bool $condition
	 * @param string $message
	 */
	public function skipIf($condition, $message = '')
	{
		$message = $message ?: 'Unknown Reason';
		if ($condition === TRUE) {
			throw new SkipException($message);
		}
	}

}
