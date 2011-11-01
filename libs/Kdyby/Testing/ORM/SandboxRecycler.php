<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\ORM;

use Doctrine\Common\Cache\AbstractCache;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class SandboxRecycler extends Nette\Object
{

	/** @var ISandboxBuilder */
	private $builder;

	/** @var boolean */
	private $schemaOn = FALSE;

	/** @var Sandbox */
	protected $sandbox;



	/**
	 * @param ISandboxBuilder $builder
	 */
	public function __construct(ISandboxBuilder $builder)
	{
		$this->builder = $builder;
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
			$container->eventManager->addEventSubscriber($container->dataFixturesListener);
		}

		return $this->sandbox = $container;
	}



	/**
	 */
	public function refreshSchema()
	{
		$em = $this->sandbox->entityManager;
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