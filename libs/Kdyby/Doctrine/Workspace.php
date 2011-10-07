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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ODM\CouchDB\DocumentManager;
use Doctrine\ODM\CouchDB\DocumentRepository;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property-read EntityManager $sqldb
 * @property-read DocumentManager $couchdb
 */
class Workspace extends Nette\Object implements Doctrine\Common\Persistence\ObjectManager
{

	/** @var array */
	private $containers = array();

	/** @var array */
	private $managers = array();

	/** @var array */
	private $repositories = array();



	/**
	 * @param array $containers
	 */
	public function __construct(array $containers)
	{
		foreach ($containers as $name => $container) {
			if (!$container instanceof IContainer) {
				throw ManagerException::objectIsNotAContainer($container);
			}

			$this->containers[$name] = $container;
		}
	}



	/**
	 * @param string $name
	 * @return Doctrine\Common\Persistence\ObjectManager
	 */
	public function &__get($name)
	{
		if (isset($this->containers[$name])) {
			return $this->containers[$name];
		}

		return parent::__get($name);
	}



	/**
	 * @param string $className
	 * @return Doctrine\Common\Persistence\ObjectManager
	 */
	public function getManager($className)
	{
		if (isset($this->managers[$className])) {
			return $this->managers[$className];
		}

		foreach ($this->containers as $container) {
			if ($container->isManaging($className)) {
				if ($container instanceof ORM\Container) {
					$this->managers[$className] = $container->getEntityManager();

				} elseif ($container instanceof ODM\Container) {
					$this->managers[$className] = $container->getDocumentManager();

				} else {
					throw new Nette\NotImplementedException;
				}

				break;
			}
		}

		if (!isset($this->managers[$className])) {
			throw ManagerException::unknownType($className);
		}

		return $this->managers[$className];
	}



	/**
	 * @return array of Doctrine\Common\Persistence\ObjectManager
	 */
	public function getManagers()
	{
		$managers = array();
		foreach ($this->containers as $container) {
			if ($container instanceof ORM\Container) {
				$managers[] = $container->getEntityManager();

			} elseif ($container instanceof ODM\Container) {
				$managers[] = $container->getDocumentManager();
			}
		}

		return $managers;
	}



	/**
	 * @return array of Doctrine\Common\EventManager
	 */
	public function getEventManagers()
	{
		$eventManagers = array();
		foreach ($this->getManagers() as $manager) {
			$eventManagers[] = $manager->getEventManager();
		}

		return $eventManagers;
	}



	/**
	 * @param string $className
	 * @return Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getRepository($className)
	{
		if (isset($this->repositories[$className])) {
			return $this->repositories[$className];
		}

		return $this->repositories[$className] = $this->getManager($className)->getRepository($className);
	}



	/**
	 * Returns the metadata descriptor for a class.
	 *
	 * @return Doctrine\Common\Persistence\Mapping\ClassMetadata
	 */
	public function getClassMetadata($className)
	{
		try {
			return $this->getManager($className)->getClassMetadata($className);

		} catch (Doctrine\ORM\Mapping\MappingException $e) {
			throw ManagerException::invalidMapping($className, $e);
		}
	}



	/**
	 * @throws Nette\NotImplementedException
	 */
	public function getMetadataFactory()
	{
		throw new Nette\NotImplementedException;
	}



	/**
	 * @throws Doctrine\ORM\OptimisticLockException
	 */
	public function flush()
	{
		foreach ($this->getManagers() as $manager) {
			$manager->flush();
		}
	}



	/**
	 * This is just a convenient shortcut for $om->getRepository($entityName)->find($id).
	 *
	 * @param string $className
	 * @param mixed $identifier
	 * @return object
	 */
	public function find($className, $identifier)
	{
		return $this->getRepository($className)->find($identifier);
	}



	/**
	 * @param string $className
	 * @param mixed $identifier
	 * @return object
	 */
	public function getReference($className, $identifier)
	{
		return $this->getManager($className)->getReference($className, $identifier);
	}



	/**
	 * @param object $object
	 * @return object
	 */
	public function persist($entity)
	{
		if (!is_object($entity)) {
			throw ManagerException::notAnObject($entity);
		}

		$this->getManager(get_class($entity))->persist($entity);
		return $entity;
	}



	/**
	 * @param object $entity
	 * @return $entity
	 */
	public function remove($entity)
	{
		if (!is_object($entity)) {
			throw ManagerException::notAnObject($entity);
		}

		$this->getManager(get_class($entity))->remove($entity);
		return $entity;
	}



	/**
	 * @param object $entity
	 * @return object
	 */
	public function refresh($entity)
	{
		if (!is_object($entity)) {
			throw ManagerException::notAnObject($entity);
		}

		$this->getManager(get_class($entity))->refresh($entity);
		return $entity;
	}



	/**
	 * @param object $entity
	 * @return object
	 */
	public function detach($entity)
	{
		if (!is_object($entity)) {
			throw ManagerException::notAnObject($entity);
		}

		$this->getManager(get_class($entity))->detach($entity);
		return $entity;
	}



	/**
	 * @param object $entity
	 * @return object
	 */
	public function merge($entity)
	{
		if (!is_object($entity)) {
			throw ManagerException::notAnObject($entity);
		}

		$this->getManager(get_class($entity))->merge($entity);
		return $entity;
	}



	/**
	 * @param object $entity
	 * @return boolean
	 */
	public function contains($entity)
	{
		if (!is_object($entity)) {
			throw ManagerException::notAnObject($entity);
		}

		return $this->getManager(get_class($entity))->contains($entity);
	}



	/**
	 * Clears all EntityManagers and DocumentManagers.
	 */
	public function clear()
	{
		foreach ($this->getManagers() as $manager) {
			$manager->clear();
		}
	}



	/**
	 * Closes all EntityManagers and DocumentManagers.
	 */
	public function close()
	{
		foreach ($this->getManagers() as $manager) {
			$manager->close();
		}
	}

}