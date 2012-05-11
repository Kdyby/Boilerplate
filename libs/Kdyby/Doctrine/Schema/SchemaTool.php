<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Schema;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SchemaTool extends Doctrine\ORM\Tools\SchemaTool
{
	const onCreateSchemaSql = 'onCreateSchemaSql';
	const onDropSchemaSql = 'onDropSchemaSql';
	const onDropDatabaseSql = 'onDropDatabaseSql';
	const onUpdateSchemaSql = 'onUpdateSchemaSql';

	/**
	 * @var \Doctrine\Common\EventManager
	 */
	private $evm;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		parent::__construct($em);
		$this->em = $em;
		$this->evm = $em->getEventManager();
	}



	/**
	 * @param array $classes
	 * @return array
	 */
	public function getCreateSchemaSql(array $classes)
	{
		$sqls = parent::getCreateSchemaSql($classes);

		if ($this->evm->hasListeners(static::onCreateSchemaSql)) {
			$eventArgs = new CreateSchemaSqlEventArgs($this->em, $classes, $sqls);
			$this->evm->dispatchEvent(static::onCreateSchemaSql, $eventArgs);
			$sqls = $eventArgs->getSqls();
		}

		return $sqls;
	}



	/**
	 * @return array
	 */
	public function getDropDatabaseSQL()
	{
		$sqls = parent::getDropDatabaseSQL();

		if ($this->evm->hasListeners(static::onDropDatabaseSql)) {
			$eventArgs = new DropDatabaseSqlEventArgs($this->em, $sqls);
			$this->evm->dispatchEvent(static::onDropDatabaseSql, $eventArgs);
			$sqls = $eventArgs->getSqls();
		}

		return $sqls;
	}



	/**
	 * @param array $classes
	 * @return array
	 */
	public function getDropSchemaSQL(array $classes)
	{
		$sqls = parent::getDropSchemaSQL($classes);

		if ($this->evm->hasListeners(static::onDropSchemaSql)) {
			$eventArgs = new DropSchemaSqlEventArgs($this->em, $classes, $sqls);
			$this->evm->dispatchEvent(static::onDropSchemaSql, $eventArgs);
			$sqls = $eventArgs->getSqls();
		}

		return $sqls;
	}



	/**
	 * @param array $classes
	 * @param bool $saveMode
	 * @return array
	 */
	public function getUpdateSchemaSql(array $classes, $saveMode = false)
	{
		$sqls = parent::getUpdateSchemaSql($classes, $saveMode);

		if ($this->evm->hasListeners(static::onUpdateSchemaSql)) {
			$eventArgs = new UpdateSchemaSqlEventArgs($this->em, $classes, $sqls);
			$this->evm->dispatchEvent(static::onUpdateSchemaSql, $eventArgs);
			$sqls = $eventArgs->getSqls();
		}

		return $sqls;
	}

}
