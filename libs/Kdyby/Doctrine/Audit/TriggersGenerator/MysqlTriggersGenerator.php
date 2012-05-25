<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Audit\TriggersGenerator;

use Doctrine;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Doctrine\Audit\AuditConfiguration;
use Kdyby\Doctrine\Mapping\ClassMetadata;
use Kdyby\Doctrine\Schema\Trigger;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MysqlTriggersGenerator extends Nette\Object
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	private $config;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;

	/**
	 * @var \Doctrine\DBAL\Platforms\MySqlPlatform
	 */
	private $platform;



	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Kdyby\Doctrine\Audit\AuditConfiguration $config
	 */
	public function __construct(EntityManager $entityManager, AuditConfiguration $config)
	{
		$this->em = $entityManager;
		$this->config = $config;
		$this->conn = $entityManager->getConnection();
		$this->platform = $this->conn->getDatabasePlatform();
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 *
	 * @throws \Kdyby\NotImplementedException
	 * @return Trigger[]
	 */
	public function generate(ClassMetadata $class, Schema $targetSchema)
	{
		if ($class->auditRelations) {
			throw new Kdyby\NotImplementedException("Sorry bro.");
		}

		$triggers = array();
		$entityTable = $class->getTableName();
		$auditTable = $this->config->prefix . $class->getTableName() . $this->config->suffix;
		$idCol = $class->getSingleIdentifierColumnName();

		// before insert
		$triggers[] = $ai = Trigger::afterInsert($class->getTableName(), 'audit')
			->declare('audit_revision', 'BIGINT')
			->insert('db_audit_revisions', array(
				'type' => 'INS',
				'className' => $class->name,
				'entityId%sql' => "NEW.`$idCol`",
				'createdAt%sql' => 'NOW()',
				'author%sql' => '@kdyby_current_user',
				'comment%sql' => '@kdyby_action_comment'
			))
			->set('audit_revision', 'LAST_INSERT_ID()')
			->insertSelect($auditTable, $targetSchema->getTable($entityTable), array(
				'values' => array('_revision%sql' => '@audit_revision'),
				'where' => "`$idCol` = NEW.`$idCol`"
			));

		$versionUpdate = function (Trigger $trigger, $action) use ($class, $idCol, $targetSchema, $auditTable, $entityTable) {
			/** @var Schema $targetSchema */
			return $trigger->declare('audit_revision', 'BIGINT')
				->insert('db_audit_revisions', array(
					'type' => $action,
					'className' => $class->name,
					'entityId%sql' => "OLD.`$idCol`",
					'createdAt%sql' => 'NOW()',
					'author%sql' => '@kdyby_current_user',
					'comment%sql' => '@kdyby_action_comment'
				))
				->set('audit_revision', 'LAST_INSERT_ID()')
				->set('audit_revision_previous', '(SELECT MAX(_revision) FROM `' . $auditTable . "` WHERE `$idCol` = OLD.`$idCol`)")
				->insertSelect($auditTable, $targetSchema->getTable($entityTable), array(
					'values' => array(
						'_revision%sql' => '@audit_revision',
						'_revision_previous%sql' => '@audit_revision_previous'
					),
					'where' => "`$idCol` = OLD.`$idCol`"
				));
		};

		// before update
		$triggers[] = $bu = $versionUpdate(Trigger::beforeUpdate($class->getTableName(), 'audit'), 'UPD');

		// before delete
		$triggers[] = $bd = $versionUpdate(Trigger::beforeDelete($class->getTableName(), 'audit'), 'DEL');

		//$beforeUpdate->add("INSERT INTO $auditTable ");

//		$sqls[] = <<<TRG
//DROP TRIGGER IF EXISTS $triggerName;
//DELIMITER //
//CREATE TRIGGER $triggerName BEFORE INSERT ON $quotedTable
//  FOR EACH ROW BEGIN
//    DECLARE `var-id` int(10) unsigned;
//    DECLARE `var-title` varchar(45);
//    DECLARE `var-body` text;
//    DECLARE `var-_revision` BIGINT UNSIGNED;
//    DECLARE revisionCursor CURSOR FOR SELECT `id`, `title`, `body` FROM $auditTable WHERE `_revision`=`var-_revision` LIMIT 1;
//
//    IF NEW.`_revision` IS NULL THEN
//      INSERT INTO $auditTable (`_revision_comment`, `_revision_user_id`, `_revision_timestamp`) VALUES (NEW.`_revision_comment`, @auth_uid, NOW());
//      SET NEW.`_revision` = LAST_INSERT_ID();
//    ELSE
//      SET `var-_revision`=NEW.`_revision`;
//      OPEN revisionCursor;
//      FETCH revisionCursor INTO `var-id`, `var-title`, `var-body`;
//      CLOSE revisionCursor;
//
//      SET NEW.`id` = `var-id`, NEW.`title` = `var-title`, NEW.`body` = `var-body`;
//    END IF;
//
//    SET NEW.`_revision_comment` = NULL;
//  END //
//DELIMITER ;
//TRG;
//
//		// after insert
//		$triggerName = $platform->quoteIdentifier($prefix . '_ai');
//		$sqls[] = <<<TRG
//DROP TRIGGER IF EXISTS $triggerName;
//DELIMITER //
//CREATE TRIGGER $triggerName AFTER INSERT ON $quotedTable
//  FOR EACH ROW BEGIN
//    UPDATE $auditTable SET `id` = NEW.`id`, `title` = NEW.`title`, `body` = NEW.`body`, `_revision_action`='INSERT' WHERE `_revision`=NEW.`_revision` AND `_revision_action` IS NULL;
//--    INSERT INTO `_revhistory_mytable` VALUES (NEW.`id`, NEW.`_revision`, @auth_uid, NOW());
//  END //
//DELIMITER ;
//TRG;

		return $triggers;
	}

}
