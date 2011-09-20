<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Kdyby;
use Nette;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Doctrine\ORM\Configuration $configurator
 * @property-read Doctrine\DBAL\Connection $connection
 * @property-read EventManager $eventManager
 * @property-read EntityManager $entityManager
 *
 * @property-read Doctrine\DBAL\Event\Listeners\MysqlSessionInit $mysqlSessionInitListener
 * @property-read Mapping\EntityDefaultsListener $entityDefaultsListener
 * @property-read Mapping\DiscriminatorMapDiscoveryListener $discriminatorMapDiscoveryListener
 *
 * @property-read Diagnostics\Panel $logger
 *
 * @property-read AnnotationReader $annotationReader
 * @property-read Mapping\Driver\AnnotationDriver $annotationDriver
 *
 * @property-read Doctrine\ORM\Tools\SchemaTool $schemaTool
 *
 * @property-read Doctrine\Common\DataFixtures\Loader $fixturesLoader
 * @property-read Doctrine\Common\DataFixtures\Purger\PurgerInterface $fixturesPurger
 * @property-read Doctrine\Common\DataFixtures\Executor\AbstractExecutor $fixturesExecutor
 * @property-read Kdyby\Testing\Database\DataFixturesListener $dataFixturesListener
 */
class Container extends Kdyby\DI\Container implements Kdyby\Doctrine\IContainer
{

	/**
	 * @return Mapping\Driver\AnnotationDriver
	 */
	protected function createServiceAnnotationDriver()
	{
		return new Mapping\Driver\AnnotationDriver($this->annotationReader, $this->params['entityDirs']);
	}



	/**
	 * @return Mapping\DiscriminatorMapDiscoveryListener
	 */
	protected function createServiceDiscriminatorMapDiscoveryListener()
	{
		return new Mapping\DiscriminatorMapDiscoveryListener($this->annotationReader, $this->annotationDriver);
	}



	/**
	 * @return Mapping\EntityDefaultsListener
	 */
	protected function createServiceEntityDefaultsListener()
	{
		return new Mapping\EntityDefaultsListener();
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
		return new Doctrine\ORM\Tools\SchemaTool($this->getEntityManager());
	}



	/**
	 * @return Doctrine\Common\DataFixtures\Loader
	 */
	protected function createServiceFixturesLoader()
	{
		return new Doctrine\Common\DataFixtures\Loader();
	}



	/**
	 * @return Doctrine\Common\DataFixtures\Purger\PurgerInterface
	 */
	protected function createServiceFixturesPurger()
	{
		return new Doctrine\Common\DataFixtures\Purger\ORMPurger($this->getEntityManager());
	}



	/**
	 * @return Doctrine\Common\DataFixtures\Executor\AbstractExecutor
	 */
	protected function createServiceFixturesExecutor()
	{
		return new Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->getEntityManager(), $this->fixturesPurger);
	}



	/**
	 * @return Kdyby\Testing\DataFixturesListener
	 */
	protected function createServiceDataFixturesListener()
	{
		return new Kdyby\Testing\Database\DataFixturesListener($this->fixturesLoader, $this->fixturesExecutor);
	}



	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @param string $entityName
	 * @return EntityRepository
	 */
	public function getRepository($entityName)
	{
		return $this->getEntityManager()->getRepository($entityName);
	}



	/**
	 * @param string $className
	 * @return bool
	 */
	public function isManaging($className)
	{
		try {
			$this->getEntityManager()->getClassMetadata($className);
			return TRUE;

		} catch (Doctrine\ORM\Mapping\MappingException $e) {
			return FALSE;
		}
	}

}