<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Database;

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
	 * @param Kdyby\Application\Container $context
	 */
	public function __construct(Kdyby\Application\Container $context)
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
			$this->schemaOn = TRUE;

			return $this->container;
		}

		return $this->refreshContainer();
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


}