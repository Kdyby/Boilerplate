<?php

namespace Kdyby\Doctrine;

use Doctrine;
use Nette;
use Kdyby;



/**
 * @property-read Doctrine\ORM\EntityManager $entityManager
 * 
 * @method mixed find($id, $lockMode, $lockVersion)
 * @method mixed findAll()
 * @method mixed findBy($criteria)
 * @method mixed findOneBy($criteria)
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class EntityService extends Service
{

	/** @var string */
	private $entityName;



	/**
	 * @param Doctrine\ORM\EntityManager $entityManager
	 * @param string $entityName
	 */
	public function __construct(Doctrine\ORM\EntityManager $em, $entityName)
	{
		parent::__construct($em);
		$this->entityName = $entityName;
	}



	/** 
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityName;
	}



	/**
	 * @return Doctrine\ORM\EntityRepository
	 */
	public function getRepository()
	{
		return $this->getEntityManager()->getRepository($this->entityName);
	}



	/**
	 * Create entity and flush
	 * @return Kdyby\Doctrine\BaseEntity
	 */
	public function create()
	{
		$args = func_get_args();
		$ref = new \ReflectionClass($this->getEntityName());
		return $ref->newInstanceArgs($args);
	}



	/**
	 * Update entity and flush
	 * @param Kdyby\Doctrine\BaseEntity entity
	 */
	public function update($entity)
	{
		if (!$entity instanceof $this->entityName) {
			throw ServiceException::invalidEntity(get_class($entity), $this->entityName);
		}

		return parent::update($entity);
	}



	/**
	 * Persist entity and flush
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @return Kdyby\Doctrine\BaseEntity
	 */
	public function save($entity)
	{
		if (!$entity instanceof $this->entityName) {
			throw ServiceException::invalidEntity(get_class($entity), $this->entityName);
		}

		return parent::save($entity);
	}



	/**
	 * Delete entity and flush
	 * @param Kdyby\Doctrine\BaseEntity entity
	 */
	public function delete($entity)
	{
		if (!$entity instanceof $this->entityName) {
			throw ServiceException::invalidEntity(get_class($entity), $this->entityName);
		}

		return parent::delete($entity);
	}



	/**
	 * @return mixed
	 */
	public function trunctate()
	{
		return parent::trunctate($this->getEntityName());
	}



	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->getRepository(), $method), $args);
	}



}