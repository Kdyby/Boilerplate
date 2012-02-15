<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\ORM;

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
	 * @return \Kdyby\Tests\ORM\SandboxRegistry
	 */
	final public function getRegistry()
	{
		$registry = $this->getContainer()->doctrine->registry;
		if (!$registry instanceof SandboxRegistry) {
			throw new Kdyby\UnexpectedValueException("Service 'doctrine' must be instance of 'Kdyby\\Tests\\ORM\\SandboxRegistry', instance of '" . get_class($registry) . "' given.");
		}

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
	 * Crawls all the entities associations, to avoid requiring of listing of all classes, required by test, by hand.
	 * Associations are gonna be discovered automatically.
	 *
	 * @param array $entities
	 */
	public function setEntities(array $entities)
	{
		foreach ($this->getAnnotationDrivers() as $driver) {
			$driver->setClassNames($entities);
		}

		$allClasses = array();
		do {
			$allClasses[] = $entity = array_shift($entities);
			$meta = $this->getRegistry()->getClassMetadata($entity);
			foreach ($meta->getAssociationNames() as $assoc) {
				$entities = array_merge($entities, array($meta->getAssociationTargetClass($assoc)));
			}

		} while ($entities = array_diff(array_unique($entities), $allClasses));

		foreach ($this->getAnnotationDrivers() as $driver) {
			$driver->setClassNames($allClasses);
		}
	}



	/**
	 * @return \Kdyby\Doctrine\Mapping\Driver\AnnotationDriver[]
	 */
	private function getAnnotationDrivers()
	{
		$drivers = array();

		foreach ($this->getRegistry()->getEntityManagers() as $em) {
			$drivers[] = $driver = $em->getConfiguration()->getMetadataDriverImpl();

			if ($driver instanceof DriverChain) {
				$drivers = array_merge($drivers, $driver->getDrivers());
			}
		}

		return array_filter($drivers, function ($driver) {
			return $driver instanceof Kdyby\Doctrine\Mapping\Driver\AnnotationDriver;
		});
	}



	/**
	 */
	public function refreshSchema()
	{
		foreach ($this->getRegistry()->getEntityManagers() as $em) {
			$schemaTool = new SchemaTool($em);

			// prepare schema
			$classes = $em->getMetadataFactory()->getAllMetadata();
			$schemaTool->createSchema($classes);
		}
	}



	/**
	 */
	public function generateProxyClasses()
	{
		foreach ($this->getRegistry()->getEntityManagers() as $em) {
			$proxyDir = $em->getConfiguration()->getProxyDir();
			@mkdir($proxyDir, 0777);

			// deleting classes
			foreach (Finder::findFiles('*Proxy.php')->in($proxyDir) as $proxy) {
				if (!@unlink($proxy->getRealpath())) {
					throw new Kdyby\IOException("Proxy class " . $proxy->getBaseName() . " cannot be deleted.");
				}
			}

			// rebuild proxies
			$classes = $em->getMetadataFactory()->getAllMetadata();
			$em->getProxyFactory()->generateProxyClasses($classes);
		}
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
