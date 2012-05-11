<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit\EventListener;

use Doctrine\ORM\Tools\ToolEvents;
use Kdyby\Doctrine\Audit\AuditManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\Common\EventSubscriber;
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
        return array(
            ToolEvents::postGenerateSchemaTable,
            ToolEvents::postGenerateSchema,
        );
    }



	/**
	 * @param \Doctrine\ORM\Tools\Event\GenerateSchemaTableEventArgs $eventArgs
	 */
    public function postGenerateSchemaTable(GenerateSchemaTableEventArgs $eventArgs)
    {
        $cm = $eventArgs->getClassMetadata();
        if (!$this->metadataFactory->isAudited($cm->name)) {
			return;
		}

		$schema = $eventArgs->getSchema();

		$entityTable = $eventArgs->getClassTable();
		$revisionTable = $schema->createTable(
			$this->config->getTablePrefix() . $entityTable->getName() . $this->config->getTableSuffix()
		);

		foreach ($entityTable->getColumns() AS $column) {
			/* @var $column \Doctrine\DBAL\Schema\Column */
			$revisionTable->addColumn($column->getName(), $column->getType()->getName(), array_merge(
				$column->toArray(),
				array('notnull' => false, 'autoincrement' => false)
			));
		}

		// revision id
		$revisionTable->addColumn($this->config->getRevisionFieldName(), $this->config->getRevisionIdFieldType());

		// revision type
		$revisionTable->addColumn($this->config->getRevisionTypeFieldName(), 'string', array('length' => 4));

		// foreing keys
		$pkColumns = $entityTable->getPrimaryKey()->getColumns();
		$pkColumns[] = $this->config->getRevisionFieldName();
		$revisionTable->setPrimaryKey($pkColumns);
    }



	/**
	 * @param \Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs $eventArgs
	 */
    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs)
    {
        $schema = $eventArgs->getSchema();
        $revisionsTable = $schema->createTable($this->config->getRevisionTableName());
        $revisionsTable->addColumn('id', $this->config->getRevisionIdFieldType(), array(
            'autoincrement' => true,
        ));
        $revisionsTable->addColumn('timestamp', 'datetime');
        $revisionsTable->addColumn('username', 'string');
        $revisionsTable->addColumn('message', 'text');

        $revisionsTable->setPrimaryKey(array('id'));
    }

}
