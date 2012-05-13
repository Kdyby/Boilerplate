<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit\Listener;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events as ORMEvents;
use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\Doctrine\Schema;
use Kdyby\Doctrine\Audit\AuditManager;
use Nette;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CreateSchemaListener extends Nette\Object implements EventSubscriber
{

	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	private $config;

	/**
	 * @var \Kdyby\Doctrine\Mapping\ClassMetadataFactory
	 */
	private $metadataFactory;

	/**
	 * @var \Doctrine\Common\Annotations\Reader
	 */
	private $reader;



	/**
	 * @param \Kdyby\Doctrine\Audit\AuditManager $auditManager
	 * @param \Doctrine\Common\Annotations\Reader $reader
	 */
	public function __construct(AuditManager $auditManager, Reader $reader)
	{
		$this->config = $auditManager->getConfiguration();
		$this->metadataFactory = $auditManager->getMetadataFactory();
		$this->reader = $reader;
	}



	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			ToolEvents::postGenerateSchemaTable,
			ORMEvents::loadClassMetadata,
			Schema\SchemaTool::onCreateSchemaSql,
			Schema\SchemaTool::onUpdateSchemaSql,
			Schema\SchemaTool::onDropSchemaSql,
		);
	}



	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(Doctrine\ORM\Event\LoadClassMetadataEventArgs $args)
	{
		/** @var \Kdyby\Doctrine\Mapping\ClassMetadata $meta */
		$meta = $args->getClassMetadata();
		$meta->setAudited($this->isEntityAudited($meta->name));
	}



	/**
	 * @param string $className
	 *
	 * @return string|NULL
	 */
	private function isEntityAudited($className)
	{
		return (bool)$this->reader->getClassAnnotation(
			Nette\Reflection\ClassType::from($className),
			'Kdyby\Doctrine\Audit\AuditedEntity'
		);
	}



	/**
	 * @param \Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $eventArgs
	 */
	public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $eventArgs)
	{
		$class = $eventArgs->getClassMetadata();
		if (!$this->metadataFactory->isAudited($class->name)) {
			return;
		}

		$schema = $eventArgs->getSchema();
		$entityTable = $eventArgs->getClassTable();
		$revisionTable = $schema->createTable($this->getClassAuditTableName($class));
		foreach ($entityTable->getColumns() AS $column) {
			/* @var $column \Doctrine\DBAL\Schema\Column */
			$revisionTable->addColumn($column->getName(), $column->getType()->getName(), array_merge(
				$column->toArray(),
				array('notnull' => false, 'autoincrement' => false)
			));
		}

		// revision id
		$revisionTable->addColumn($this->config->fieldName, 'integer');

		// foreing keys
		$pkColumns = $entityTable->getPrimaryKey()->getColumns();
		$pkColumns[] = $this->config->fieldName;
		$revisionTable->setPrimaryKey($pkColumns);
	}



	/**
	 * @param \Kdyby\Doctrine\Schema\CreateSchemaSqlEventArgs $args
	 */
	public function onCreateSchemaSql(Schema\CreateSchemaSqlEventArgs $args)
	{
		$sqls = $args->getSqls();

		foreach ($args->getClasses() as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
			}

			$prefix = $this->getClassAuditTriggerPrefix($class);
			//$sqls[] = 'DROP TRIGGER IF EXISTS ' . $prefix . '_';
		}

		$args->setSqls($sqls);
	}



	/**
	 * @param \Kdyby\Doctrine\Schema\UpdateSchemaSqlEventArgs $args
	 */
	public function onUpdateSchemaSql(Schema\UpdateSchemaSqlEventArgs $args)
	{
		$sqls = $args->getSqls();

		foreach ($args->getClasses() as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
			}

			$prefix = $this->getClassAuditTriggerPrefix($class);
			//$sqls[] = 'DROP TRIGGER IF EXISTS ' . $prefix . '_';
		}

		$args->setSqls($sqls);
	}



	/**
	 * @param \Kdyby\Doctrine\Schema\DropSchemaSqlEventArgs $args
	 */
	public function onDropSchemaSql(Schema\DropSchemaSqlEventArgs $args)
	{
		$sqls = $args->getSqls();

		foreach ($args->getClasses() as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
			}

			$prefix = $this->getClassAuditTriggerPrefix($class);
			//$sqls[] = 'DROP TRIGGER IF EXISTS ' . $prefix . '_';
		}

		$args->setSqls($sqls);
	}



	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 *
	 * @return string
	 */
	private function getClassAuditTableName(ClassMetadata $class)
	{
		return $this->config->prefix . $class->getTableName() . $this->config->suffix;
	}



	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 */
	private function getClassAuditTriggerPrefix(ClassMetadata $class)
	{
		return $this->getClassAuditTableName($class) . '_audit';
	}

}
