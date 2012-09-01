<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit\Listener;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Schema;
use Doctrine\DBAL\Platforms;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events as ORMEvents;
use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;
use Doctrine\ORM\Tools\ToolEvents;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby;
use Kdyby\Doctrine\Audit\AuditManager;
use Kdyby\Doctrine\Audit\AuditConfiguration;
use Kdyby\Doctrine\Audit\TriggersGenerator\MysqlTriggersGenerator;
use Kdyby\Doctrine\Type;
use Kdyby\Doctrine\Schema\SchemaTool;
use Kdyby\Doctrine\Schema\CreateSchemaSqlEventArgs;
use Kdyby\Doctrine\Schema\UpdateSchemaSqlEventArgs;
use Kdyby\Doctrine\Schema\DropSchemaSqlEventArgs;
use Nette;



/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip@prochazka.su>
 */
class CreateSchemaListener extends Nette\Object implements Doctrine\Common\EventSubscriber
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
			SchemaTool::onCreateSchemaSql,
			SchemaTool::onUpdateSchemaSql,
			SchemaTool::onDropSchemaSql,
		);
	}



	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(Doctrine\ORM\Event\LoadClassMetadataEventArgs $args)
	{
		/** @var \Kdyby\Doctrine\Mapping\ClassMetadata $meta */
		$meta = $args->getClassMetadata();
		if ($meta->rootEntityName && $meta->rootEntityName !== $meta->name) {
			$meta = $args->getEntityManager()->getClassMetadata($meta->rootEntityName);
		}

		/** @var \Kdyby\Doctrine\Audit\AuditedEntity $audited */
		$classRefl = Nette\Reflection\ClassType::from($meta->name);
		$audited = $this->reader->getClassAnnotation($classRefl, 'Kdyby\Doctrine\Audit\AuditedEntity');

		if ($audited) {
			$meta->setAudited((bool)$audited);
			$meta->auditRelations = array_merge((array)$meta->auditRelations, (array)$audited->related);
		}
	}



	/**
	 * @param \Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $eventArgs
	 *
	 * @throws \Kdyby\NotImplementedException
	 */
	public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $eventArgs)
	{
		/** @var \Kdyby\Doctrine\Mapping\ClassMetadata $class */
		$class = $eventArgs->getClassMetadata();
		if (!$this->metadataFactory->isAudited($class->name)) {
			return;
		}

		if ($class->auditRelations) {
			throw new Kdyby\NotImplementedException("Sorry bro.");
		}

		$schema = $eventArgs->getSchema();
		if (!$schema->hasTable($revisionTableName = $this->getClassAuditTableName($class))) {
			$this->doCreateRevisionTable($eventArgs->getClassTable(), $schema, $revisionTableName);
		}
	}



	/**
	 * @param \Doctrine\DBAL\Schema\Table $entityTable
	 * @param \Doctrine\DBAL\Schema\Schema $schema
	 * @param string $revisionTableName
	 */
	private function doCreateRevisionTable(Schema\Table $entityTable, Schema\Schema $schema, $revisionTableName)
	{
		$historyTable = new Schema\Table('db_audit_revisions', array(
			new Schema\Column('id', Type::getType('integer'))
		));

		$revisionTable = $schema->createTable($revisionTableName);
		foreach ($entityTable->getColumns() AS $column) {
			/* @var $column \Doctrine\DBAL\Schema\Column */
			$revisionTable->addColumn($column->getName(), $column->getType()->getName(), array_merge(
				$column->toArray(),
				array('notnull' => false, 'autoincrement' => false)
			));
		}

		// revision id
		$revisionTable->addColumn(AuditConfiguration::REVISION_ID, 'bigint', array('notnull' => TRUE));
		$revisionTable->addColumn(AuditConfiguration::REVISION_PREVIOUS, 'bigint', array('notnull' => FALSE));

		// primary
		$pkColumns = $entityTable->getPrimaryKey()->getColumns();
		$pkColumns[] = AuditConfiguration::REVISION_ID;
		$revisionTable->setPrimaryKey($pkColumns);

		// revision fk
		$revisionTable->addForeignKeyConstraint( // todo: config/constants
			$historyTable,
			array(AuditConfiguration::REVISION_ID),
			array('id')
		);

		// previous revision index & fk
		$revisionTable->addIndex(array(AuditConfiguration::REVISION_ID), 'idx_rev_id');
		$revisionTable->addIndex(array(AuditConfiguration::REVISION_PREVIOUS), 'idx_previous_rev');
		$revisionTable->addForeignKeyConstraint(
			$revisionTable,
			array(AuditConfiguration::REVISION_PREVIOUS),
			array(AuditConfiguration::REVISION_ID)
		);
	}



	/**
	 * @param \Kdyby\Doctrine\Schema\CreateSchemaSqlEventArgs $args
	 */
	public function onCreateSchemaSql(CreateSchemaSqlEventArgs $args)
	{
		$args->addSqls($this->generateTriggers(
			$args->getEntityManager(),
			$args->getClasses(),
			$args->getTargetSchema()
		));
	}



	/**
	 * @param \Kdyby\Doctrine\Schema\UpdateSchemaSqlEventArgs $args
	 */
	public function onUpdateSchemaSql(UpdateSchemaSqlEventArgs $args)
	{
		$args->addSqls($this->generateTriggers(
			$args->getEntityManager(),
			$args->getClasses(),
			$args->getTargetSchema()
		));
	}



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param array $classes
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 *
	 * @return array
	 */
	private function generateTriggers(EntityManager $em, array $classes, Schema\Schema $targetSchema)
	{
		$connection = $em->getConnection();
		$platform = $connection->getDatabasePlatform();
		if (!$platform instanceof Platforms\MySqlPlatform) {
			return array();
		}

		$sqls = array();
		foreach ($classes as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
			}

			$generator = new MysqlTriggersGenerator($em, $this->config);
			foreach ($generator->generate($class, $targetSchema) as $trigger) {
				$sqls[] = $trigger->getDropSql();
				$sqls[] = (string)$trigger;
			}
		}

		return $sqls;
	}



	/**
	 * @param \Kdyby\Doctrine\Schema\DropSchemaSqlEventArgs $args
	 */
	public function onDropSchemaSql(DropSchemaSqlEventArgs $args)
	{
		$platform = $args->getEntityManager()->getConnection()->getDatabasePlatform();
		if (!$platform instanceof Platforms\MySqlPlatform) {
			return;
		}

		$sqls = array();
		foreach ($args->getClasses() as $class) {
			if (!$this->metadataFactory->isAudited($class->name)) {
				continue;
			}

			//$sqls[] = 'DROP TRIGGER IF EXISTS ' . $prefix . '_';
		}

		$args->addSqls($sqls);
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

}
