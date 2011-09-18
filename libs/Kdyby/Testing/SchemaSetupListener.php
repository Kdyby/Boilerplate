<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use DoctrineExtensions\PHPUnit\Event\EntityManagerEventArgs;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */

class SchemaSetupListener extends Nette\Object implements Doctrine\Common\EventSubscriber
{

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'preTestSetUp'
		);
	}



	/**
	 * @param EntityManagerEventArgs $eventArgs
	 */
	public function preTestSetUp(EntityManagerEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$classes = $em->getMetadataFactory()->getAllMetadata();

		$schemaTool = new SchemaTool($em);
		$schemaTool->dropDatabase();
		$schemaTool->createSchema($classes);
	}

}