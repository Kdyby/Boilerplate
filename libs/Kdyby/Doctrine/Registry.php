<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMException;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;



/**
 * References all Doctrine connections and entity managers in a given Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Registry extends Nette\Object
{
	/** @var \Nette\DI\Container */
	protected $container;

	/** @var array */
	private $connections;

	/** @var array */
	private $entityManagers;

	/** @var string */
	private $defaultConnection;

	/** @var string */
	private $defaultEntityManager;



	/**
	 * @param \Nette\DI\Container $container
	 * @param array $connections
	 * @param array $entityManagers
	 * @param string $defaultConnection
	 * @param string $defaultEntityManager
	 */
	public function __construct(Nette\DI\Container $container, array $connections, array $entityManagers, $defaultConnection, $defaultEntityManager)
	{
		$this->container = $container;
		$this->connections = $connections;
		$this->entityManagers = $entityManagers;
		$this->defaultConnection = $defaultConnection;
		$this->defaultEntityManager = $defaultEntityManager;
	}



	/**
	 * Gets the default connection name.
	 *
	 * @return string The default connection name
	 */
	public function getDefaultConnectionName()
	{
		return $this->defaultConnection;
	}



	/**
	 * Gets the named connection.
	 *
	 * @param string $name The connection name (null for the default one)
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection($name = NULL)
	{
		if ($name === NULL) {
			$name = $this->defaultConnection;
		}

		if (!isset($this->connections[$name])) {
			throw new Kdyby\InvalidArgumentException('Doctrine Connection named "' . $name . '" does not exist.');
		}

		return $this->container->getService($this->connections[$name]);
	}



	/**
	 * Gets an array of all registered connections
	 *
	 * @return \Doctrine\DBAL\Connection[] An array of Connection instances
	 */
	public function getConnections()
	{
		$connections = array();
		foreach ($this->connections as $name => $id) {
			$connections[$name] = $this->container->getService($id);
		}

		return $connections;
	}



	/**
	 * Gets all connection names.
	 *
	 * @return array An array of connection names
	 */
	public function getConnectionNames()
	{
		return $this->connections;
	}



	/**
	 * Gets the default entity manager name.
	 *
	 * @return string The default entity manager name
	 */
	public function getDefaultEntityManagerName()
	{
		return $this->defaultEntityManager;
	}



	/**
	 * Gets a named entity manager.
	 *
	 * @param string $name The entity manager name (null for the default one)
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager($name = NULL)
	{
		if ($name === NULL) {
			$name = $this->defaultEntityManager;
		}

		if (!isset($this->entityManagers[$name])) {
			throw new Kdyby\InvalidArgumentException('Doctrine EntityManager named "' . $name . '" does not exist.');
		}

		return $this->container->getService($this->entityManagers[$name]);
	}



	/**
	 * Gets an array of all registered entity managers
	 *
	 * @return \Doctrine\ORM\EntityManager[] An array of EntityManager instances
	 */
	public function getEntityManagers()
	{
		$ems = array();
		foreach ($this->entityManagers as $name => $id) {
			$ems[$name] = $this->container->getService($id);
		}

		return $ems;
	}



	/**
	 * Resets a named entity manager.
	 *
	 * This method is useful when an entity manager has been closed
	 * because of a rollbacked transaction AND when you think that
	 * it makes sense to get a new one to replace the closed one.
	 *
	 * Be warned that you will get a brand new entity manager as
	 * the existing one is not useable anymore. This means that any
	 * other object with a dependency on this entity manager will
	 * hold an obsolete reference. You can inject the registry instead
	 * to avoid this problem.
	 *
	 * @param string $name The entity manager name (null for the default one)
	 */
	public function resetEntityManager($name = NULL)
	{
		if ($name === NULL) {
			$name = $this->defaultEntityManager;
		}

		if (!isset($this->entityManagers[$name])) {
			throw new Kdyby\InvalidArgumentException('Doctrine EntityManager named "' . $name . '" does not exist.');
		}

		// force the creation of a new entity manager
		// if the current one is closed
		$this->container->removeService($this->entityManagers[$name]);
	}



	/**
	 * Resolves a registered namespace alias to the full namespace.
	 *
	 * This method looks for the alias in all registered entity managers.
	 *
	 * @param string $alias The alias
	 * @return string The full namespace
	 * @see Configuration::getEntityNamespace
	 */
	public function getEntityNamespace($alias)
	{
		foreach ($this->getEntityManagers() as $em) {
			try {
				return $em->getConfiguration()->getEntityNamespace($alias);
			} catch (ORMException $e) { }
		}

		throw ORMException::unknownEntityNamespace($alias);
	}



	/**
	 * Gets all connection names.
	 *
	 * @return array An array of connection names
	 */
	public function getEntityManagerNames()
	{
		return $this->entityManagers;
	}



	/**
	 * Gets the EntityRepository for an entity.
	 *
	 * @param string $entityName        The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Doctrine\ORM\EntityRepository
	 */
	public function getRepository($entityName, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		return $this->getDao($entityName, $entityManagerName);
	}



	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName        The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function getDao($entityName, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		return $this->getEntityManager($entityManagerName)->getRepository($entityName);
	}



	/**
	 * Gets the Dao for an entity.
	 *
	 * @param string $entityName        The name of the entity.
	 * @param string $entityManagerName The entity manager name (null for the default one)
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	public function getClassMetadata($entityName, $entityManagerName = NULL)
	{
		if (!class_exists($entityName = is_object($entityName) ? get_class($entityName) : $entityName)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$entityName' given");
		}

		return $this->getEntityManager($entityManagerName)->getClassMetadata($entityName);
	}



	/**
	 * Gets the entity manager associated with a given class.
	 *
	 * @param string $className A Doctrine Entity class name
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return \Doctrine\ORM\EntityManager|NULL
	 */
	public function getEntityManagerForClass($className)
	{
		if (!class_exists($className = is_object($className) ? get_class($className) : $className)) {
			throw new Kdyby\InvalidArgumentException("Expected entity name, '$className' given");
		}

		$proxyClass = ClassType::from($className);
		$className = $proxyClass->getName();
		if ($proxyClass->implementsInterface('Doctrine\ORM\Proxy\Proxy')) {
			$className = $proxyClass->getParentClass()->getName();
		}

		foreach ($this->getEntityManagers() as $em) {
			if (!$em->getConfiguration()->getMetadataDriverImpl()->isTransient($className)) {
				return $em;
			}
		}
	}

}
