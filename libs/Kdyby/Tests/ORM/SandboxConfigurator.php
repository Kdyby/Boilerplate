<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\ORM;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\DriverChain;
use Doctrine\ORM\Tools\SchemaTool;
use Kdyby;
use Nette;
use Nette\Utils\Finder;



/**
 * Inception!
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SandboxConfigurator extends Kdyby\Config\Configurator
{

	/** @var array */
	private $entities = array();



	/**
	 * @param array $params
	 */
	public function __construct($params = NULL)
	{
		parent::__construct($params, Kdyby\Framework::createPackagesList());
		$this->setEnvironment('console');
		$this->setProductionMode(TRUE);
	}



	/**
	 * @throws \Kdyby\UnexpectedValueException
	 * @return \Kdyby\Tests\ORM\SandboxRegistry
	 */
	final public function getRegistry()
	{
		/** @var \Kdyby\Tests\ORM\SandboxRegistry $registry */
		$registry = $this->getContainer()->doctrine->registry;
		if (!$registry instanceof SandboxRegistry) {
			throw new Kdyby\UnexpectedValueException("Service 'doctrine' must be instance of 'Kdyby\\Tests\\ORM\\SandboxRegistry', instance of '" . get_class($registry) . "' given.");
		}
		$registry->setConfigurator($this);
		return $registry;
	}



	/**
	 * @return string
	 */
	public function getConfigFile()
	{
		return $this->parameters['appDir'] . '/config.orm.neon';
	}



	/**
	 * @param \Doctrine\ORM\EntityManager $manager
	 */
	public function configureManager(EntityManager $manager)
	{
		$this->configureEntities($manager);
		$this->refreshSchema($manager);
		$this->generateProxyClasses($manager);
	}



	/**
	 * @param \Doctrine\ORM\EntityManager $manager
	 */
	private function configureEntities(EntityManager $manager)
	{
		if (!$this->entities) {
			return;
		}

		$entities = $this->entities;
		foreach ($this->getAnnotationDrivers($manager) as $driver) {
			$driver->setClassNames($entities);
		}

		$allClasses = array();
		do {
			$allClasses[] = $entity = array_shift($entities);
			$class = $manager->getClassMetadata($entity);
			/** @var \Kdyby\Doctrine\Mapping\ClassMetadata $class */
			foreach ($class->getAssociationNames() as $assoc) {
				$entities = array_merge($entities, array($class->getAssociationTargetClass($assoc)));
			}

			if ($root = $class->rootEntityName) {
				$class = $manager->getClassMetadata($root);
				$entities = array_merge($entities, array_values($class->discriminatorMap));
			}

		} while ($entities = array_diff(array_unique($entities), $allClasses));

		foreach ($this->getAnnotationDrivers($manager) as $driver) {
			$driver->setClassNames($allClasses);
		}
	}



	/**
	 * Crawls all the entities associations, to avoid requiring of listing of all classes, required by test, by hand.
	 * Associations are gonna be discovered automatically.
	 * Lazily.
	 *
	 * @param array $entities
	 */
	public function setEntities(array $entities = NULL)
	{
		$this->entities = $entities ?: array();
	}



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 * @return \Kdyby\Doctrine\Mapping\Driver\AnnotationDriver[]
	 */
	private function getAnnotationDrivers(EntityManager $em)
	{
		$drivers = array();

		$drivers[] = $driver = $em->getConfiguration()->getMetadataDriverImpl();
		if ($driver instanceof DriverChain) {
			/** @var \Doctrine\ORM\Mapping\Driver\DriverChain $driver */
			$drivers = array_merge($drivers, $driver->getDrivers());
		}

		return array_filter($drivers, function ($driver) {
			return $driver instanceof Kdyby\Doctrine\Mapping\Driver\AnnotationDriver;
		});
	}



	/**
	 * Prepare schema
	 *
	 * @param \Doctrine\ORM\EntityManager $em
	 */
	private function refreshSchema(EntityManager $em)
	{
		$schemaTool = new SchemaTool($em);
		$classes = $em->getMetadataFactory()->getAllMetadata();
		$schemaTool->createSchema($classes);
	}



	/**
	 * @param \Doctrine\ORM\EntityManager $em
	 *
	 * @throws \Kdyby\IOException
	 */
	private function generateProxyClasses(EntityManager $em)
	{
		$proxyDir = $em->getConfiguration()->getProxyDir();
		@mkdir($proxyDir, 0777);

		// deleting classes
		foreach (Finder::findFiles('*Proxy.php')->in($proxyDir) as $proxy) {
			/** @var \SplFileInfo $proxy */
			if (!@unlink($proxy->getRealpath())) {
				throw new Kdyby\IOException("Proxy class " . $proxy->getBaseName() . " cannot be deleted.");
			}
		}

		// rebuild proxies
		$classes = $em->getMetadataFactory()->getAllMetadata();
		$em->getProxyFactory()->generateProxyClasses($classes);
	}



	/**
	 * Setups the Debugger defaults
	 *
	 * @param array $params
	 */
	protected function setupDebugger($params = array())
	{
		// pass
	}

}
