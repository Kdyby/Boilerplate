<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Packages\DoctrinePackage;

use Doctrine;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMException;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;
use Symfony;
use Symfony\Component\DependencyInjection\ContainerInterface;



/**
 * References all Doctrine connections and entity managers in a given Container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Registry extends Nette\Object implements Symfony\Bridge\Doctrine\RegistryInterface
{
	/** @var \Symfony\Component\DependencyInjection\ContainerInterface */
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
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->connections = $container->getParameter('doctrine.connections');
        $this->entityManagers = $container->getParameter('doctrine.entity_managers');
        $this->defaultConnection = $container->getParameter('doctrine.default_connection');
        $this->defaultEntityManager = $container->getParameter('doctrine.default_entity_manager');
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

        return $this->container->get($this->connections[$name]);
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
            $connections[$name] = $this->container->get($id);
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

        return $this->container->get($this->entityManagers[$name]);
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
            $ems[$name] = $this->container->get($id);
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
        $this->container->set($this->entityManagers[$name], NULL);
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
     * @param string $entityManagerNAme The entity manager name (null for the default one)
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getRepository($entityName, $entityManagerName = NULL)
    {
        return $this->getDao($entityName, $entityManagerName);
    }



    /**
     * Gets the Dao for an entity.
     *
     * @param string $entityName        The name of the entity.
     * @param string $entityManagerNAme The entity manager name (null for the default one)
     * @return \Kdyby\Doctrine\Dao
     */
    public function getDao($entityName, $entityManagerName = NULL)
    {
        return $this->getEntityManager($entityManagerName)->getRepository($entityName);
    }



    /**
     * Gets the entity manager associated with a given class.
     *
     * @param string $class A Doctrine Entity class name
     * @return \Doctrine\ORM\EntityManager|NULL
     */
    public function getEntityManagerForClass($class)
    {
        $proxyClass = ClassType::from($class);
        if ($proxyClass->implementsInterface('Doctrine\ORM\Proxy\Proxy')) {
            $class = $proxyClass->getParentClass()->getName();
        }

        foreach ($this->getEntityManagers() as $em) {
            if (!$em->getConfiguration()->getMetadataDriverImpl()->isTransient($class)) {
                return $em;
            }
        }
    }

}
