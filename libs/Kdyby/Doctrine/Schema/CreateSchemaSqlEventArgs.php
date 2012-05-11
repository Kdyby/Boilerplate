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
class CreateSchemaSqlEventArgs extends Doctrine\Common\EventArgs
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
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Doctrine\ORM\Mapping\ClassMetadata[] $classes
	 * @param array $sqls
	 */
	public function __construct(EntityManager $entityManager, array $classes, array $sqls)
	{
		$this->em = $entityManager;
		$this->classes = $classes;
		$this->sqls = $sqls;
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
	public function setSqls(array $sqls)
	{
		$this->sqls = $sqls;
	}



	/**
	 * @return array
	 */
	public function getSqls()
	{
		return $this->sqls;
	}

}
