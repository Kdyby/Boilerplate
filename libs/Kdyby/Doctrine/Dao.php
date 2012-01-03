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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Kdyby;
use Kdyby\Persistence\IDao;
use Kdyby\Persistence\IQueryObject;
use Kdyby\Doctrine\Mapping\EntityValuesMapper;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method Mapping\ClassMetadata getClassMetadata() getClassMetadata()
 */
class Dao extends Doctrine\ORM\EntityRepository implements IDao, Kdyby\Persistence\IQueryable, Kdyby\Persistence\IObjectFactory
{

	/** @var EntityValuesMapper */
	private $entityMapper;



	/**
	 * @param array $arguments Arguments for entity's constructor
	 * @param array $values Values to be set via mapper
	 *
	 * @return object
	 */
	public function createNew($arguments = array(), $values = array())
	{
		$class = $this->getEntityName();
		if (!$arguments) {
			$entity = new $class;

		} else {
			$reflection = new Nette\Reflection\ClassType($class);
			$entity = $reflection->newInstanceArgs($arguments);
		}

		if ($values) {
			if (!$this->entityMapper) {
				throw new Kdyby\InvalidArgumentException("EntityMapper service was not injected, therefore DAO cannot set values.");

			} else {
				$this->entityMapper->load($entity, $values);
			}
		}
		return $entity;
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\EntityValuesMapper $mapper
	 */
	public function setEntityMapper(EntityValuesMapper $mapper)
	{
		$this->entityMapper = $mapper;
	}



	/**
	 * Persists given entities, but does not flush.
	 *
	 * @param object|array|Collection $entity
	 * @return object|array
	 */
	public function add($entity)
	{
		if ($entity instanceof Collection) {
			return $this->add($entity->toArray());

		} elseif (is_array($entity)) {
			return array_map(array($this, 'add'), $entity);

		} elseif (!$entity instanceof $this->_entityName) {
			throw new Kdyby\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ", instanceof '" . get_class($entity) . "' given.");
		}

		$this->getEntityManager()->persist($entity);
		return $entity;
	}



	/**
	 * Persists given entities and flushes all to the storage.
	 *
	 * @param object|array|Collection $entity
	 * @return object|array
	 */
	public function save($entity = NULL)
	{
		if ($entity === NULL) {
			$this->flush();
			return;
		}

		$result = $this->add($entity);
		$this->flush();
		return $result;
	}



	/**
	 * @param object|array|Collection $entity
	 * @param boolean $withoutFlush
	 */
	public function delete($entity, $withoutFlush = IDao::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->delete($entity->toArray(), $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			array_map(function ($entity) use ($repository) {
				return $repository->delete($entity, IDao::NO_FLUSH);
			}, $entity);

			$this->flush($withoutFlush);
			return;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Kdyby\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->getEntityManager()->remove($entity);
		$this->flush($withoutFlush);
	}



	/**
	 * @param boolean $withoutFlush
	 */
	protected function flush($withoutFlush = IDao::FLUSH)
	{
		if ($withoutFlush === IDao::FLUSH) {
			try {
				$this->getEntityManager()->flush();

			} catch (\PDOException $e) {
				throw new SqlException($e);
			}
		}
	}



	/**
	 * @param string $alias
	 * @return Doctrine\ORM\QueryBuilder|Doctrine\CouchDB\View\AbstractQuery $qb
	 */
	public function createQueryBuilder($alias = NULL)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();

		if ($alias !== NULL) {
			$qb->select($alias)->from($this->getEntityName(), $alias);
		}

		return $qb;
	}



	/**
	 * @param string $dql
	 *
	 * @return \Doctrine\ORM\Query
	 */
	public function createQuery($dql = NULL)
	{
		return $this->getEntityManager()->createQuery($dql);
	}



	/**
	 * @param callable $callback
	 * @return mixed|boolean
	 */
	public function transactional($callback)
	{
		$connection = $this->getEntityManager()->getConnection();
		$connection->beginTransaction();

		try {
			$return = callback($callback)->invoke($this);
			$this->flush();
			$connection->commit();
			return $return ?: TRUE;

		} catch (\Exception $e) {
			$connection->rollback();
			throw $e;
		}
	}



	/**
	 * @param \Kdyby\Persistence\IQueryObject $queryObject
	 * @return integer
	 */
	public function count(IQueryObject $queryObject)
	{
		try {
			return $queryObject->count($this->getEntityManager()->createQueryBuilder());

		} catch (\Exception $e) {
			return $this->handleQueryExceptions($e, $queryObject);
		}
	}



	/**
	 * @param \Kdyby\Persistence\IQueryObject $queryObject
	 * @return array
	 */
	public function fetch(IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetch($this);

		} catch (\Exception $e) {
			return $this->handleQueryExceptions($e, $queryObject);
		}
	}



	/**
	 * @param \Kdyby\Persistence\IQueryObject $queryObject
	 * @return object
	 */
	public function fetchOne(IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetchOne($this);

		} catch (NoResultException $e) {
			return NULL;

		} catch (NonUniqueResultException $e) { // this should never happen!
			throw new Kdyby\InvalidStateException("You have to setup your query using ->setMaxResult(1).", NULL, $e);

		} catch (\Exception $e) {
			return $this->handleQueryExceptions($e, $queryObject);
		}
	}



	/**
	 * @param integer|array $id
	 * @return \Doctrine\ORM\Proxy\Proxy
	 */
	public function getReference($id)
	{
		return $this->getEntityManager()->getReference($this->_entityName, $id);
	}



	/**
	 * @param \Exception $e
	 * @param \Kdyby\Persistence\IQueryObject $queryObject
	 *
	 * @throws \Exception
	 */
	private function handleQueryExceptions(\Exception $e, IQueryObject $queryObject)
	{
		if ($e instanceof Doctrine\ORM\Query\QueryException) {
			throw new QueryException($e, '('. get_class($queryObject) . ') ' . $e->getMessage(), $queryObject->getLastQuery());

		} elseif ($e instanceof \PDOException) {
			throw new SqlException($e, NULL, $queryObject->getLastQuery(), '('. get_class($queryObject) . ') ' . $e->getMessage());

		} else {
			throw $e;
		}
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
