<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Nette;
use Kdyby\Doctrine\Mapping\ClassMetadataFactory;



/**
 * Audit Manager grants access to metadata and configuration
 * and has a getter, similar to getRepository() on Entity Manager,
 * that returns Audit Reader for given class.
 *
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip@prochazka.su>
 */
class AuditManager extends Nette\Object
{

	/**
	 * @var \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	private $config;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Kdyby\Doctrine\Audit\ChangeLog
	 */
	private $history;



	/**
	 * @param \Kdyby\Doctrine\Audit\AuditConfiguration $config
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	public function __construct(AuditConfiguration $config, EntityManager $em)
	{
		$this->config = $config;
		$this->em = $em;
	}



	/**
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadataFactory
	 */
	public function getMetadataFactory()
	{
		return $this->em->getMetadataFactory();
	}



	/**
	 * @return \Kdyby\Doctrine\Audit\AuditConfiguration
	 */
	public function getConfiguration()
	{
		return $this->config;
	}



	/**
	 * @param string $className
	 *
	 * @throws \Kdyby\NotImplementedException
	 */
	public function getAuditReader($className)
	{
		throw new \Kdyby\NotImplementedException;
	}



	/**
	 * @throws \Kdyby\NotImplementedException
	 * @return \Kdyby\Doctrine\Audit\ChangeLog
	 */
	public function getChangeLog()
	{
		throw new \Kdyby\NotImplementedException;
	}

}
