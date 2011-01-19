<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Application;

Use Doctrine;
use Nette;



/**
 * @property-read \Doctrine\ORM\EntityManager $entityManager
 *
 * @method void clear() clear()
 * @method void flush() flush()
 * @method void remove() remove(BaseEntity $entity)
 * @method void refresh() refresh(BaseEntity $entity)
 * @method void beginTransaction() beginTransaction()
 * @method void commit() commit()
 * @method void rollback() rollback()
 *
 * @author Jan Smitka
 */
class DatabaseManager extends Nette\Context
{

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var array */
	private $services = array();



	/**
	 * Adds the specified service to the service container.
	 * @param  string service name
	 * @param  mixed  object, class name or factory callback
	 * @param  bool   is singleton?
	 * @param  array  factory options
	 * @return void
	 */
	public function addService($name, $service, $singleton = TRUE, array $options = NULL)
	{
		parent::addService($name, $service, $singleton, $options);
		$this->services[] = $name;
	}



	/**
	 * @return array
	 */
	public function getRegisteredServices()
	{
		return $this->services;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @param Doctrine\ORM\EntityManager $entityManager
	 */
	public function setEntityManager(Doctrine\ORM\EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}



	/**
	 * @param object $entity
	 */
	public function persist($entity)
	{
		$this->getEntityManager()->persist($entity);
	}



	/**
	 * @param object $entity
	 * @param mixed $version
	 */
	public function lock($entity, $version)
	{
		$this->getEntityManager()->lock($entity, Doctrine\DBAL\LockMode::OPTIMISTIC, $version);
	}



	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->getEntityManager(), $name), $arguments);
	}

}