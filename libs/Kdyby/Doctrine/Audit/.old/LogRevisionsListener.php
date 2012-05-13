<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit\Listener;

use Kdyby\Doctrine\Audit\AuditManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LogRevisionsListener extends Nette\Object implements EventSubscriber
{

	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	private $config;

	/**
	 * @var
	 */
	private $metadataFactory;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;

	/**
	 * @var \Doctrine\DBAL\Platforms\AbstractPlatform
	 */
	private $platform;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var array
	 */
	private $insertRevisionSQL = array();

	/**
	 * @var \Doctrine\ORM\UnitOfWork
	 */
	private $uow;

	/**
	 * @var int
	 */
	private $revisionId;



	/**
	 * @param \Kdyby\Doctrine\Audit\AuditManager $auditManager
	 */
	public function __construct(AuditManager $auditManager)
	{
		$this->config = $auditManager->getConfiguration();
		$this->metadataFactory = $auditManager->getMetadataFactory();
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(Events::onFlush, Events::postPersist, Events::postUpdate);
	}



	public function postPersist(LifecycleEventArgs $eventArgs)
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getEntity();

		$class = $this->em->getClassMetadata(get_class($entity));
		if (!$this->metadataFactory->isAudited($class->name)) {
			return;
		}

		$this->saveRevisionEntityData($class, $this->getOriginalEntityData($entity), 'INS');
	}



	public function postUpdate(LifecycleEventArgs $eventArgs)
	{
		// onFlush was executed before, everything already initialized
		$entity = $eventArgs->getEntity();

		$class = $this->em->getClassMetadata(get_class($entity));
		if (!$this->metadataFactory->isAudited($class->name)) {
			return;
		}

		$entityData = array_merge($this->getOriginalEntityData($entity), $this->uow->getEntityIdentifier($entity));
		$this->saveRevisionEntityData($class, $entityData, 'UPD');
	}



	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$this->em = $eventArgs->getEntityManager();
		$this->conn = $this->em->getConnection();
		$this->uow = $this->em->getUnitOfWork();
		$this->platform = $this->conn->getDatabasePlatform();
		$this->revisionId = null; // reset revision

		foreach ($this->uow->getScheduledEntityDeletions() AS $entity) {
			$class = $this->em->getClassMetadata(get_class($entity));
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
			}
			$entityData = array_merge($this->getOriginalEntityData($entity), $this->uow->getEntityIdentifier($entity));
			$this->saveRevisionEntityData($class, $entityData, 'DEL');
		}
	}



	/**
	 * get original entity data, including versioned field, if "version" constraint is used
	 *
	 * @param mixed $entity
	 *
	 * @return array
	 */
	private function getOriginalEntityData($entity)
	{
		$class = $this->em->getClassMetadata(get_class($entity));
		$data = $this->uow->getOriginalEntityData($entity);
		if ($class->isVersioned) {
			$versionField = $class->versionField;
			$data[$versionField] = $class->reflFields[$versionField]->getValue($entity);
		}
		return $data;
	}



	private function getRevisionId()
	{
		if ($this->revisionId === null) {
			$date = date_create("now")->format($this->platform->getDateTimeFormatString());
			$this->conn->insert($this->config->getTableName(), array(
				'timestamp' => $date,
				'username' => $this->config->getCurrentUser(),
			));
			$this->revisionId = $this->conn->lastInsertId();
		}
		return $this->revisionId;
	}



	private function getInsertRevisionSQL($class)
	{
		if (!isset($this->insertRevisionSQL[$class->name])) {
			$tableName = $this->config->getTablePrefix() . $class->table['name'] . $this->config->getTableSuffix();
			$sql = "INSERT INTO " . $tableName . " (" .
				$this->config->getFieldName() . ", " . $this->config->getRevisionTypeFieldName();
			foreach ($class->fieldNames AS $field) {
				$sql .= ', ' . $class->getQuotedColumnName($field, $this->platform);
			}
			$assocs = 0;
			foreach ($class->associationMappings AS $assoc) {
				if (($assoc['type'] & ClassMetadata::TO_ONE) > 0 && $assoc['isOwningSide']) {
					foreach ($assoc['targetToSourceKeyColumns'] as $sourceCol) {
						$sql .= ', ' . $sourceCol;
						$assocs++;
					}
				}
			}
			$sql .= ") VALUES (" . implode(", ", array_fill(0, count($class->fieldNames) + $assocs + 2, '?')) . ")";
			$this->insertRevisionSQL[$class->name] = $sql;
		}
		return $this->insertRevisionSQL[$class->name];
	}



	/**
	 * @param ClassMetadata $class
	 * @param array $entityData
	 * @param string $revType
	 */
	private function saveRevisionEntityData($class, $entityData, $revType)
	{
		$params = array($this->getRevisionId(), $revType);
		$types = array(\PDO::PARAM_INT, \PDO::PARAM_STR);
		foreach ($class->fieldNames AS $field) {
			$params[] = $entityData[$field];
			$types[] = $class->fieldMappings[$field]['type'];
		}
		foreach ($class->associationMappings AS $field => $assoc) {
			if (($assoc['type'] & ClassMetadata::TO_ONE) > 0 && $assoc['isOwningSide']) {
				$relatedId = $entityData[$field] !== NULL
					? $this->uow->getEntityIdentifier($entityData[$field])
					: array();

				$targetClass = $this->em->getClassMetadata($assoc['targetEntity']);

				foreach ($assoc['sourceToTargetKeyColumns'] as $sourceColumn => $targetColumn) {
					if ($relatedId) {
						$params[] = $relatedId[$targetClass->fieldNames[$targetColumn]];
						$types[] = $targetClass->getTypeOfColumn($targetColumn);

					} else {
						$params[] = null;
						$types[] = \PDO::PARAM_STR;
					}
				}
			}
		}

		$this->conn->executeUpdate($this->getInsertRevisionSQL($class), $params, $types);
	}
}
