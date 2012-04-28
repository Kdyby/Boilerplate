<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations\Tools;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TableDumper extends Nette\Object implements \Iterator
{

	const ROWS_COUNT = 3;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $connection;

	/**
	 * @var \Doctrine\DBAL\Platforms\AbstractPlatform
	 */
	private $platform;

	/**
	 * @var \Doctrine\ORM\Tools\SchemaTool
	 */
	private $schemaTool;

	/**
	 * @var \Kdyby\Doctrine\Mapping\ClassMetadata[]
	 */
	private $metadata;

	/**
	 * @var array
	 */
	private $tablesLeft;

	/**
	 * @var resource|\Doctrine\DBAL\Driver\Statement
	 */
	private $tableData;

	/**
	 * @var string
	 */
	private $current;

	/**
	 * @var int
	 */
	private $rows = 0;



	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata[] $metadata
	 */
	public function __construct(EntityManager $entityManager, array $metadata)
	{
		$this->entityManager = $entityManager;
		$this->connection = $entityManager->getConnection();
		$this->platform = $this->connection->getDatabasePlatform();
		$this->schemaTool = new Doctrine\ORM\Tools\SchemaTool($this->entityManager);

		$this->metadata = $metadata;
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata[] $metadata
	 *
	 * @return array
	 */
	protected function collectTables(array $metadata)
	{
		$tables = array();
		foreach ($metadata as $class) {
			$tables[] = $class->getTableName();
			foreach ($class->getAssociationMappings() as $assoc) {
				if (isset($assoc['joinTable'])) {
					$tables[] = $assoc['joinTable']['name'];
				}
			}
		}

		return array_unique($tables);
	}



	/**
	 * @return string
	 */
	protected function fetchOne()
	{
		if (!$this->tablesLeft) {
			return $this->current = NULL;
		}

		$table = reset($this->tablesLeft);
		if (!$this->tableData) {
			$this->tableData = $this->connection->prepare("SELECT * FROM `$table`");
			$this->tableData->execute();
		}

		$insert = new SqlInsertQuery($table, $this->connection);
		while ($row = $this->tableData->fetch(\PDO::FETCH_ASSOC)) {
			$insert->addRow($row);
			if (count($insert) === static::ROWS_COUNT) {
				break;
			}
		}

		if (!$row) {
			$this->tableData = NULL; // no more results
			array_shift($this->tablesLeft);
		}

		$this->rows++;
		return $this->current = (string)$insert;
	}



	/**
	 */
	public function rewind()
	{
		$this->tablesLeft = $this->collectTables($this->metadata);
		$this->tableData = NULL;
		$this->rows = 0;
		$this->fetchOne();
	}



	/**
	 * @returns string
	 */
	public function current()
	{
		return $this->current;
	}



	/**
	 */
	public function next()
	{
		return $this->fetchOne();
	}



	/**
	 */
	public function key()
	{
		return $this->rows;
	}



	/**
	 */
	public function valid()
	{
		return (bool)$this->current;
	}

}
