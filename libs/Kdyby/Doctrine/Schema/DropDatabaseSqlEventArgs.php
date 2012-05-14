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
class DropDatabaseSqlEventArgs extends Doctrine\Common\EventArgs
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var array
	 */
	private $sqls;



	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param array $sqls
	 */
	public function __construct(EntityManager $entityManager, array $sqls)
	{
		$this->em = $entityManager;
		$this->sqls = $sqls;
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

}
