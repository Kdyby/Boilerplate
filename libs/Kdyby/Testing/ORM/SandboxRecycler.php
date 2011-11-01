<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\ORM;

use Kdyby;
use Kdyby\Doctrine\Sandbox;
use Kdyby\Doctrine\SandboxBuilder;
use Nette;



/**
 * @author Filip Procházka
 */
class SandboxRecycler extends Nette\Object
{

	/** @var SandboxBuilder */
	private $builder;

	/** @var boolean */
	private $schemaOn = FALSE;

	/** @var Sandbox */
	protected $sandbox;



	/**
	 * @param Nette\DI\Container $context
	 * @param array $entities
	 */
	public function __construct(Nette\DI\Container $context, array $entities)
	{
		$this->builder = new SandboxBuilder();

		$this->builder->params['driver'] = 'pdo_sqlite';
		$this->builder->params['memory'] = TRUE;

		if ($entities) {
			$this->builder->params['entityNames'] = $entities;
		}

		$this->builder->expandParams($context);
	}



	/**
	 * @return Sandbox
	 */
	public function refresh()
	{
		if ($this->schemaOn === FALSE) {
			$this->refreshContainer();
			$this->refreshSchema();
			$this->generateProxyClasses();
			$this->schemaOn = TRUE;

			return $this->sandbox;
		}

		$this->sandbox->entityManager->clear();
		$this->refreshContainer();
		$this->truncateDatabase();
		return $this->sandbox;
	}



	/**
	 * @return Sandbox
	 */
	public function getSandbox()
	{
		return $this->sandbox;
	}



	/**
	 * @return Sandbox
	 */
	public function refreshContainer()
	{
		$container = $this->builder->build();
		if ($this->sandbox === NULL) {
			// only when container is created for the first time
			$evm = $container->getEntityManager()->getEventManager();
			$evm->addEventSubscriber($container->dataFixturesListener);
		}

		return $this->sandbox = $container;
	}



	/**
	 */
	public function refreshSchema()
	{
		$em = $this->sandbox->getEntityManager();
		$schemaTool = $this->sandbox->schemaTool;

		// prepare schema
		$classes = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool->dropDatabase();
		$schemaTool->createSchema($classes);
	}



	/**
	 */
	public function truncateDatabase()
	{
		$conn = $this->sandbox->entityManager->getConnection();

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
		$em = $this->sandbox->entityManager;
		$proxyDir = $em->getConfiguration()->getProxyDir();

		// Whether use only few entities, or more
		$params = $this->sandbox->params;
		$classNames = isset($params['entityNames']) ? $params['entityNames'] : NULL;

		// class names to delete
		if ($classNames === NULL) {
			$proxies = Nette\Utils\Finder::findFiles('*Proxy.php')->in($proxyDir);

		} else {
			$classNames = array_map(function ($class) {
				return str_replace('\\', '', $class) . 'Proxy.php';
			}, $classNames);

			$proxies = Nette\Utils\Finder::findFiles($classNames)->in($proxyDir);
		}

		// deleting classes
		foreach ($proxies as $proxy) {
			if (!@unlink($proxy->getRealpath())) {
				throw new Nette\IOException("Proxy class " . $proxy->getBaseName() . " cannot be deleted.");
			}
		}

		// rebuild proxies
		$metas = $em->getMetadataFactory()->getAllMetadata();
		$em->getProxyFactory()->generateProxyClasses($metas);
	}

}