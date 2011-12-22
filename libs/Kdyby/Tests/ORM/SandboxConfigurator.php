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
		parent::__construct($params, new Kdyby\Package\DefaultPackages());
		$this->setEnvironment('console');
		$this->setProductionMode(TRUE);
	}



	/**
	 * @return \Kdyby\Tests\ORM\SandboxRegistry
	 */
	final public function getRegistry()
	{
		$registry = $this->getContainer()->doctrine;
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
	 * @param array $entities
	 */
	public function setEntities(array $entities)
	{
		foreach ($this->getAnnotationDrivers() as $driver) {
			$driver->setClassNames($entities);
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
	 * @param array $params
	 */
	protected static function setupDebugger(array $params)
	{
		// pass
	}



	/**
	 * @param array $params
	 */
	protected static function setupDebuggerMode(array $params)
	{
		// pass
	}

}
