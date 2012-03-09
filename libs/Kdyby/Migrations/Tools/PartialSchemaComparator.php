<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
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
class PartialSchemaComparator extends Nette\Object
{

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
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->connection = $entityManager->getConnection();
		$this->platform = $this->connection->getDatabasePlatform();
		$this->schemaTool = new Doctrine\ORM\Tools\SchemaTool($this->entityManager);
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata[] $metadata
	 *
	 * @return array
	 */
	public function compare(array $metadata)
	{
		$fromSchema = $this->connection->getSchemaManager()->createSchema();
		$toSchema = $this->schemaTool->getSchemaFromMetadata($this->getAllMetadata());

		$comparator = new \Doctrine\DBAL\Schema\Comparator();
		$schemaDiff = $comparator->compare($fromSchema, $toSchema);

		$allowedTables = $this->collectTables($metadata);

		foreach ($schemaDiff->newTables as $table => $tableDiff) {
			if (!in_array(Strings::lower($table), $allowedTables, TRUE)) {
				unset($schemaDiff->newTables[$table]);
			}
		}

		foreach ($schemaDiff->changedTables as $table => $tableDiff) {
			if (!in_array(Strings::lower($table), $allowedTables, TRUE)) {
				unset($schemaDiff->changedTables[$table]);
			}
		}

		foreach ($schemaDiff->removedTables as $table => $tableDiff) {
			if (!in_array(Strings::lower($table), $allowedTables, TRUE)) {
				unset($schemaDiff->removedTables[$table]);
			}
		}

		return $schemaDiff->toSql($this->platform);
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
			$tables[] = Strings::lower($class->getTableName());
			foreach ($class->getAssociationMappings() as $assoc) {
				if (isset($assoc['joinTable'])) {
					$tables[] = Strings::lower($assoc['joinTable']['name']);
				}
			}
		}

		return array_unique($tables);
	}



	/**
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata[]
	 */
	protected function getAllMetadata()
	{
		return $this->entityManager->getMetadataFactory()->getAllMetadata();
	}

}
