<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Schema;

use Doctrine;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class UpdateSchemaSqlEventArgs extends Doctrine\Common\EventArgs
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var array|\Doctrine\ORM\Mapping\ClassMetadata[]
	 */
	private $classes;

	/**
	 * @var array
	 */
	private $sqls;

	/**
	 * @var \Doctrine\DBAL\Schema\Schema
	 */
	private $targetSchema;



	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Doctrine\ORM\Mapping\ClassMetadata[] $classes
	 * @param array $sqls
	 * @param \Doctrine\DBAL\Schema\Schema $targetSchema
	 */
	public function __construct(EntityManager $entityManager, array $classes, array $sqls, Schema $targetSchema = NULL)
	{
		$this->em = $entityManager;
		$this->classes = $classes;
		$this->sqls = $sqls;
		$this->targetSchema = $targetSchema;
	}



	/**
	 * @return array|\Doctrine\ORM\Mapping\ClassMetadata[]
	 */
	public function getClasses()
	{
		return $this->classes;
	}



	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->em;
	}



	/**
	 * @param array $sqls
	 */
	public function addSqls(array $sqls)
	{
		$this->sqls = array_merge($this->sqls, array_map(function ($sql) {
			return (string)$sql;
		}, $sqls));
	}



	/**
	 * @return array
	 */
	public function getSqls()
	{
		return $this->sqls;
	}



	/**
	 * @return \Doctrine\DBAL\Schema\Schema
	 */
	public function getTargetSchema()
	{
		return $this->targetSchema;
	}

}
