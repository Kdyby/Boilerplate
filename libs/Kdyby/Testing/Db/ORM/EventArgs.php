<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Db\ORM;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Testing\OrmTestCase;
use Nette;



/**
 * @author Filip Procházka
 */
class EventArgs extends Doctrine\Common\EventArgs
{

	/** @var EntityManager */
	private $em;

	/** @var OrmTestCase */
	private $testCase;



	/**
	 * @param EntityManager $em
	 * @param OrmTestCase $testCase
	 */
	public function __construct(EntityManager $em, OrmTestCase $testCase)
	{
		$this->em = $em;
		$this->testCase = $testCase;
	}



	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->em;
	}



	/**
	 * @return OrmTestCase
	 */
	public function getTestCase()
	{
		return $this->testCase;
	}

}