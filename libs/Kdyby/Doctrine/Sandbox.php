<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine\Common\DataFixtures;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Mapping\Driver\Driver as MappingDriver;
use Kdyby;
use Kdyby\Testing\Db\ORM\DataFixturesListener;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read Configuration $configurator
 * @property-read Connection $connection
 * @property-read EventManager $eventManager
 * @property-read EntityManager $entityManager
 *
 * @property-read AnnotationReader $annotationReader
 * @property-read MappingDriver $mappingDriver
 *
 * @property-read Diagnostics\Panel $logger
 *
 * @property-read SchemaTool $schemaTool
 *
 * @property-read DataFixtures\Loader $fixturesLoader
 * @property-read DataFixtures\Purger\PurgerInterface $fixturesPurger
 * @property-read DataFixtures\Executor\AbstractExecutor $fixturesExecutor
 * @property-read DataFixturesListener $dataFixturesListener
 */
class Sandbox extends Nette\DI\Container
{

	/**
	 * @return Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		$driver = new Mapping\Driver\AnnotationDriver($this->annotationReader);

		if (isset($this->params['entityNames'])) {
			$driver->setClassNames($this->params['entityNames']);

		} elseif (isset($this->params['entityDirs'])) {
			$driver->addPaths($this->params['entityDirs']);
		}

		return $driver;
	}



	/**
	 * @return EntityManager
	 */
	protected function createServiceEntityManager()
	{
		return EntityManager::create($this->connection, $this->configuration, $this->eventManager);
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

}