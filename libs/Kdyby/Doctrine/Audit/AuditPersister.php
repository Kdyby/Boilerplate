<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Audit;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Nette;
use Kdyby\Doctrine\Mapping\ClassMetadataFactory;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuditPersister extends Nette\Object
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;

	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	private $config;

	/**
	 * @var \Kdyby\Doctrine\Mapping\ClassMetadataFactory
	 */
	private $metadataFactory;

	/**
	 * @var \Doctrine\DBAL\Platforms\AbstractPlatform
	 */
	private $platform;



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \Kdyby\Doctrine\Audit\AuditConfiguration $config
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadataFactory $factory
	 */
	public function __construct(EntityManager $em, AuditConfiguration $config, ClassMetadataFactory $factory)
	{
		$this->em = $em;
		$this->conn = $em->getConnection();
		$this->config = $config;
		$this->metadataFactory = $factory;
		$this->platform = $this->conn->getDatabasePlatform();
	}



	/**
	 * @param string $className
	 * @param mixed $id
	 * @param int $revision
	 *
	 * @throws AuditException
	 * @return object
	 */
	public function loadRevisions($className, $id, $revision)
	{
		if (!$this->metadataFactory->isAudited($className)) {
			throw AuditException::notAudited($className);
		}

		$class = $this->em->getClassMetadata($className);

		$id = !is_array($id) ? array($id) : $id;
		$values = array_merge(array($revision), array_values($id));

		$query = $this->platform->modifyLimitQuery(
			'SELECT ' . $this->getSqlColumnsForClass($class, 'c') .
			' FROM ' . $this->getTableForClass($class) . ' c ' .
			' WHERE ' . $this->getSqlWhereIdentifierForClass($class, 'c', $this->config->getRevisionFieldName() . ' <= ?') .
			' ORDER BY c.rev DESC', 1
		);

		if ($revisionData = $this->conn->fetchAll($query, $values)) {
			return $this->createEntity($class->name, $revisionData[0]);
		}

		return NULL;
	}



	/**
	 * Return a list of all revisions.
	 *
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return Revision[]
	 */
	public function findRevisionHistory($limit = 20, $offset = 0)
	{
		$query = $this->platform->modifyLimitQuery(
			"SELECT * FROM " . $this->config->getRevisionTableName() . " ORDER BY id DESC", $limit, $offset
		);
		$revisionsData = $this->conn->fetchAll($query);

		$revisions = array();
		foreach ($revisionsData AS $row) {
			$revisions[] = $this->createRevision($row);
		}
		return $revisions;
	}



	/**
	 * Return a list of ChangedEntity instances created at the given revision.
	 *
	 * @param int $revision
	 *
	 * @return ChangedEntity[]
	 */
	public function findEntitiesChangedAtRevision($revision)
	{
		$changedEntities = array();
		foreach ($this->metadataFactory->getAllAudited() AS $class) {
			$query = 'SELECT ' . $this->getSqlColumnsForClass($class, 'r') .
				' FROM ' . $this->getTableForClass($class) . ' r ' .
				' WHERE r.' . $this->config->getRevisionFieldName() . " = ?";

			$revisionsData = $this->conn->executeQuery($query, array($revision));

			foreach ($revisionsData AS $row) {
				$id = array();
				foreach ($class->identifier AS $idField) {
					$id[$idField] = $row[$idField]; // TODO: doesn't work with composite foreign keys yet.
				}

				$changedEntities[] = new ChangedEntity(
					$class->name,
					$id,
					$row[$this->config->getRevisionTypeFieldName()],
					$this->createEntity($class->name, $row)
				);
			}
		}

		return $changedEntities;
	}



	/**
	 * Return the revision object for a particular revision.
	 *
	 * @param  int $rev
	 *
	 * @return Revision
	 */
	public function findRevision($rev)
	{
		$query = "SELECT * FROM " . $this->config->getRevisionTableName() . " r WHERE r.id = ?";
		$revisionsData = $this->conn->fetchAll($query, array($rev));

		return count($revisionsData) == 1
			? $this->createRevision($revisionsData[0])
			: NULL;
	}



	/**
	 * Find all revisions that were made of entity class with given id.
	 *
	 * @param string $className
	 * @param mixed $id
	 *
	 * @return Revision[]
	 */
	public function findRevisions($className, $id)
	{
		if (!$this->metadataFactory->isAudited($className)) {
			throw AuditException::notAudited($className);
		}

		$class = $this->em->getClassMetadata($className);

		$query = 'SELECT r.* FROM ' . $this->config->getRevisionTableName() . ' r ' .
			' INNER JOIN ' . $this->getTableForClass($class) . ' c ' .
			' ON r.id = c.' . $this->config->getRevisionFieldName() .
			' WHERE ' . $this->getSqlWhereIdentifierForClass($class, 'c') .
			' ORDER BY r.id DESC';

		$id = !is_array($id) ? array($id) : $id;
		$revisionsData = $this->conn->fetchAll($query, array_values($id));

		$revisions = array();
		foreach ($revisionsData AS $row) {
			$revisions[] = $this->createRevision($row);
		}

		return $revisions;
	}



	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 *
	 * @return string
	 */
	private function getTableForClass(ClassMetadata $class)
	{
		return $this->config->getTablePrefix() . $class->table['name'] . $this->config->getTableSuffix();
	}



	/**
	 * @param ClassMetadata $class
	 * @param string $alias
	 * @param string|array $prepend
	 *
	 * @return string
	 */
	private function getSqlWhereIdentifierForClass(ClassMetadata $class, $alias = 'c', $prepend = NULL)
	{
		$whereSQL = (array)$prepend;
		foreach ($class->identifier AS $idField) {
			if (isset($class->fieldMappings[$idField])) {
				$whereSQL[] = $alias . '.' . $class->fieldMappings[$idField]['columnName'] . ' = ?';

			} elseif (isset($class->associationMappings[$idField])) {
				$whereSQL[] = $alias . '.' . $class->associationMappings[$idField]['joinColumns'][0] . ' = ?';
			}
		}

		return implode(' AND ', $whereSQL);
	}



	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 * @param string $alias
	 *
	 * @return string
	 */
	private function getSqlColumnsForClass(ClassMetadata $class, $alias = 'c')
	{
		$columnList = array();
		foreach ($class->fieldNames AS $field) {
			$columnList[] = $alias . '.' . $class->getQuotedColumnName($field, $this->platform) . ' AS ' . $field;
		}

		foreach ($class->associationMappings AS $assoc) {
			if (($assoc['type'] & ClassMetadata::TO_ONE) > 0 && $assoc['isOwningSide']) {
				foreach ($assoc['targetToSourceKeyColumns'] as $sourceCol) {
					$columnList[] = $alias . '.' . $sourceCol;
				}
			}
		}

		return implode(', ', $columnList);
	}



	/**
	 * @param array $revision
	 * @return \Kdyby\Doctrine\Audit\Revision
	 */
	private function createRevision(array $revision)
	{
		return new Revision(
			$revision['id'],
			\DateTime::createFromFormat($this->platform->getDateTimeFormatString(), $revision['timestamp']),
			$revision['username']
		);
	}



	/**
	 * Simplified and stolen code from UnitOfWork::createEntity.
	 *
	 * @notice Creates an old version of the entity, HOWEVER related associations are all managed entities!!
	 *
	 * @param string $className
	 * @param array $data
	 *
	 * @return object
	 */
	private function createEntity($className, array $data)
	{
		$class = $this->em->getClassMetadata($className);
		$entity = $class->newInstance();

		foreach ($data as $field => $value) {
			if (isset($class->fieldMappings[$field])) {
				/** @var \Doctrine\DBAL\Types\Type $type */
				$type = Type::getType($class->fieldMappings[$field]['type']);
				$value = $type->convertToPHPValue($value, $this->platform);
				$class->setFieldValue($entity, $field, $value);
			}
		}

		foreach ($class->associationMappings as $field => $assoc) {
			// Check if the association is not among the fetch-joined associations already.
			if (isset($hints['fetched'][$className][$field])) {
				continue;
			}

			$targetClass = $this->em->getClassMetadata($assoc['targetEntity']);

			if ($assoc['type'] & ClassMetadata::TO_ONE) {
				if ($assoc['isOwningSide']) {
					$associatedId = array();
					foreach ($assoc['targetToSourceKeyColumns'] as $targetColumn => $srcColumn) {
						$joinColumnValue = isset($data[$srcColumn]) ? $data[$srcColumn] : null;
						if ($joinColumnValue !== null) {
							$associatedId[$targetClass->fieldNames[$targetColumn]] = $joinColumnValue;
						}
					}

					if (!$associatedId) {
						// Foreign key is NULL
						$class->setFieldValue($entity, $field, NULL);

					} else {
						$associatedEntity = $this->em->getReference($targetClass->name, $associatedId);
						$class->setFieldValue($entity, $field, $associatedEntity);
					}

				} else {
					// Inverse side of x-to-one can never be lazy
					$value = $this->getEntityPersister($assoc['targetEntity'])
						->loadOneToOneEntity($assoc, $entity);

					$class->setFieldValue($entity, $field, $value);
				}

			} else {
				// Inject collection
				$class->setFieldValue($entity, $field, new ArrayCollection);
			}
		}

		return $entity;
	}



	/**
	 * @param $entity
	 *
	 * @return \Doctrine\ORM\Persisters\BasicEntityPersister
	 */
	private function getEntityPersister($entity)
	{
		$uow = $this->em->getUnitOfWork();
		return $uow->getEntityPersister($entity);
	}

}
