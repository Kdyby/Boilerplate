<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\ORM;

use Doctrine;
use Doctrine\Common\DataFixtures;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Doctrine\ORM\Configuration $configurator
 * @property-read Doctrine\DBAL\Connection $connection
 * @property-read Doctrine\Common\EventManager $eventManager
 * @property-read Doctrine\ORM\EntityManager $entityManager
 *
 * @property-read Doctrine\Common\Annotations\AnnotationReader $annotationReader
 * @property-read Kdyby\Doctrine\Mapping\Driver\AnnotationDriver $annotationDriver
 *
 * @property-read Kdyby\Doctrine\Diagnostics\Panel $logger
 *
 * @property-read Doctrine\ORM\Tools\SchemaTool $schemaTool
 *
 * @property-read DataFixtures\Loader $fixturesLoader
 * @property-read DataFixtures\Purger\PurgerInterface $fixturesPurger
 * @property-read DataFixtures\Executor\AbstractExecutor $fixturesExecutor
 * @property-read DataFixturesListener $dataFixturesListener
 */
class Sandbox extends Nette\DI\Container
{

	/**
	 * @return Kdyby\Doctrine\Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		$driver = new Kdyby\Doctrine\Mapping\Driver\AnnotationDriver($this->annotationReader);

		if (isset($this->params['entityNames'])) {
			$driver->setClassNames($this->params['entityNames']);

		} elseif (isset($this->params['entityDirs'])) {
			$driver->addPaths($this->params['entityDirs']);
		}

		return $driver;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	protected function createServiceEntityManager()
	{
		return Doctrine\ORM\EntityManager::create($this->connection, $this->configuration, $this->eventManager);
	}



	/**
	 * @return Doctrine\ORM\Tools\SchemaTool
	 */
	protected function createServiceSchemaTool()
	{
		return new Doctrine\ORM\Tools\SchemaTool($this->entityManager);
	}



	/**
	 * @return DataFixtures\Loader
	 */
	protected function createServiceFixturesLoader()
	{
		return new DataFixtures\Loader();
	}



	/**
	 * @return DataFixtures\Purger\PurgerInterface
	 */
	protected function createServiceFixturesPurger()
	{
		return new DataFixtures\Purger\ORMPurger($this->entityManager);
	}



	/**
	 * @return DataFixtures\Executor\AbstractExecutor
	 */
	protected function createServiceFixturesExecutor()
	{
		return new DataFixtures\Executor\ORMExecutor($this->entityManager, $this->fixturesPurger);
	}



	/**
	 * @return DataFixturesListener
	 */
	protected function createServiceDataFixturesListener()
	{
		return new DataFixturesListener($this->fixturesLoader, $this->fixturesExecutor);
	}

}