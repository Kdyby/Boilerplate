<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Tools;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Platforms\AbstractPlatform as Platform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\DBAL\Types\Type;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NonLockingUniqueInserter extends Nette\Object
{
	/** @var EntityManager */
	private $em;

	/** @var Connection */
	private $connection;

	/** @var Platform */
	private $platform;



	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
		$this->connection = $em->getConnection();
		$this->platform = $this->connection->getDatabasePlatform();
	}



	/**
	 * When entity have columns for required associations, this will fail.
	 * Calls $em->flush().
	 *
	 * @todo fix error codes! PDO is returning database-specific codes
	 * @param object $entity
	 */
	public function persist($entity)
	{
		$this->connection->beginTransaction();

		try {
			$this->doInsert($entity);
			$this->connection->commit();
			return TRUE;

		} catch (\PDOException $e) {
			$this->connection->rollback();

			if ($e->getCode() == 23000) { // unique fail
				return FALSE;

			} else { // other fail
				throw $e;
			}

		} catch (\Exception $e) {
			$this->connection->rollback();
			throw $e;
		}
	}



	/**
	 * @param object $entity
	 */
	private function doInsert($entity)
	{
		// get entity metadata
		$meta = $this->em->getClassMetadata(get_class($entity));

		// fields that have to be inserted
		$fields = $this->getUniqueAndRequiredFields($meta);

		// read values to insert
		$values = $this->getInsertValues($meta, $entity, $fields);

		// prepare statement && execute
		$this->prepareInsert($meta, $values)->execute();

		// assign ID to entity
		if ($idGen = $meta->idGenerator) {
			if ($idGen->isPostInsertGenerator()) {
				$id = $idGen->generate($this->em, $entity);
				$identifierFields = $meta->getIdentifierFieldNames();
				$meta->setFieldValue($entity, reset($identifierFields), $id);
			}
		}

		// entity is now safely inserted to database, merge now
		$this->em->merge($entity);
		$this->em->flush();
	}



	/**
	 * @param ClassMetadata $meta
	 * @param array $values
	 * @param array $types
	 * @return Statement
	 */
	private function prepareInsert(ClassMetadata $meta, array $values)
	{
		// construct sql
		$columns = array_map(callback($meta, 'getColumnName'), array_keys($values));
		$insertSql = 'INSERT INTO ' . $meta->getQuotedTableName($this->platform)
			. ' (' . implode(', ', $columns) . ')'
			. ' VALUES (' . implode(', ', array_fill(0, count($columns), '?')) . ')';

		// create statement
		$statement = $this->connection->prepare($insertSql);

		// fetch column types
		$types = $this->getColumnsTypes($meta, array_keys($values));

		// bind values
		$paramIndex = 1;
		foreach ($values as $field => $value) {
			$statement->bindValue($paramIndex++, $value, $types[$field]);
		}

		return $statement;
	}



	/**
	 * @param ClassMetadata $meta
	 * @return array
	 */
	private function getUniqueAndRequiredFields(ClassMetadata $meta)
	{
		$fields = array();
		foreach ($meta->getFieldNames() as $fieldName) {
			$mapping = $meta->getFieldMapping($fieldName);
			if (!empty($mapping['id'])) { // not an id
				continue;
			}

			if (empty($mapping['nullable'])) { // is not nullable
				$fields[] = $fieldName;
				continue;
			}

			if (!empty($mapping['unique'])) { // is unique
				$fields[] = $fieldName;
				continue;
			}
		}
		return $fields;
	}



	/**
	 * @param ClassMetadata $meta
	 * @param object $entity
	 * @param array $fields
	 * @return array
	 */
	private function getInsertValues(ClassMetadata $meta, $entity, array $fields)
	{
		$values = array();
		foreach ($fields as $fieldName) {
			$values[$fieldName] = $meta->getFieldValue($entity, $fieldName);
		}
		return $values;
	}



	/**
	 * @param ClassMetadata $meta
	 * @param array $fields
	 * @return array
	 */
	private function getColumnsTypes(ClassMetadata $meta, array $fields)
	{
		$columnTypes = array();
		foreach ($fields as $fieldName) {
			$columnTypes[$fieldName] = Type::getType($meta->fieldMappings[$fieldName]['type']);
		}
		return $columnTypes;
	}

}
