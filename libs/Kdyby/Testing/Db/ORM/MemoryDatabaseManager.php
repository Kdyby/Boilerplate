<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Db\ORM;

use Kdyby;
use Kdyby\Doctrine\ORM\Container;
use Kdyby\Doctrine\ORM\ContainerBuilder;
use Nette;



/**
 * @author Filip Procházka
 */
class MemoryDatabaseManager extends Nette\Object
{

	/** @var ContainerBuilder */
	private $containerBuilder;

	/** @var boolean */
	private $schemaOn = FALSE;

	/** @var Container */
	protected $container;



	/**
	 * @param Kdyby\DI\Container $context
	 */
	public function __construct(Kdyby\DI\Container $context)
	{
		$this->containerBuilder = new ContainerBuilder($context->doctrineCache, array(
				'driver' => 'pdo_sqlite',
				//'dsn' => 'sqlite::memory:',
				'memory' => TRUE
			));

		$this->containerBuilder->registerTypes();
		$this->containerBuilder->registerAnnotationClasses();
		$this->containerBuilder->expandParams($context);
	}



	/**
	 * @return Container
	 */
	public function refresh()
	{
		if ($this->schemaOn === FALSE) {
			$this->refreshContainer();
			$this->refreshSchema();
			$this->generateProxyClasses();
			$this->schemaOn = TRUE;

			return $this->container;
		}

		$this->refreshContainer();
		$this->truncateDatabase();
		return $this->container;
	}



	/**
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
	}



	/**
	 * @return Container
	 */
	public function refreshContainer()
	{
		$container = $this->containerBuilder->build();
		if ($this->container === NULL) {
			// only when container is created for the first time
			$evm = $container->getEntityManager()->getEventManager();
			$evm->addEventSubscriber($container->dataFixturesListener);
		}

		return $this->container = $container;
	}



	/**
	 */
	public function refreshSchema()
	{
		$em = $this->container->getEntityManager();
		$schemaTool = $this->container->schemaTool;

		// prepare schema
		$classes = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool->dropDatabase();
		$schemaTool->createSchema($classes);
	}



	/**
	 */
	public function truncateDatabase()
	{
		$conn = $this->container->entityManager->getConnection();

		$conn->beginTransaction();
        try {
			foreach ($conn->getSchemaManager()->listTableNames() as $tableName) {
				$query = $conn->getDatabasePlatform()->getTruncateTableSql($tableName);
				$conn->executeUpdate($query);
			}
			$conn->commit();

		} catch (\Exception $e) {
			$conn->rollback();
			throw $e;
		}
	}



	/**
	 */
	public function generateProxyClasses()
	{
		$em = $this->container->entityManager;
		$proxyDir = $em->getConfiguration()->getProxyDir();
		foreach (Nette\Utils\Finder::findFiles('*Proxy.php')->in($proxyDir) as $proxy) {
			if (!@unlink($proxy->getRealpath())) {
				throw new Nette\IOException("Proxy class " . $proxy->getBaseName() . " cannot be deleted.");
			}
		}

		$metas = $em->getMetadataFactory()->getAllMetadata();
		$em->getProxyFactory()->generateProxyClasses($metas);
	}

}