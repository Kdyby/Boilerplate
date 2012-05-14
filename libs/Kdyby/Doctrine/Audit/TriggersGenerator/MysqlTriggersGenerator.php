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
use Doctrine\ORM\EntityManager;
use Kdyby;
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
	 * @var \Doctrine\DBAL\Connection
	 */
	private $conn;

	/**
	 * @var \Doctrine\DBAL\Platforms\MySqlPlatform
	 */
	private $platform;



	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->em = $entityManager;
		$this->conn = $entityManager->getConnection();
		$this->platform = $this->conn->getDatabasePlatform();
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @return array
	 */
	public function generate(ClassMetadata $class)
	{
		$triggers = array();
//			$platform = $connection->getDatabasePlatform();
//			$quotedTable = $class->getQuotedTableName($platform);
//			$auditTable = $platform->quoteIdentifier($this->getClassAuditTableName($class));

		// before insert
		$triggers[] = $afterInsert = new Trigger($class->getTableName(), 'audit', Trigger::DO_AFTER, Trigger::ACTION_INSERT);

		// before update
		$triggers[] = $beforeUpdate = new Trigger($class->getTableName(), 'audit', Trigger::DO_BEFORE, Trigger::ACTION_UPDATE);

		// before delete
		$triggers[] = $beforeDelete = new Trigger($class->getTableName(), 'audit', Trigger::DO_BEFORE, Trigger::ACTION_DELETE);

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
