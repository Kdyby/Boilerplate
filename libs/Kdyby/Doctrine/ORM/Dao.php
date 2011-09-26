<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Kdyby;
use Kdyby\Doctrine\IQueryObject;
use Kdyby\Doctrine\Mapping\EntityValuesMapper;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 *
 * @method Mapping\ClassMetadata getClassMetadata() getClassMetadata()
 */
class Dao extends Doctrine\ORM\EntityRepository implements Kdyby\Doctrine\IDao, Kdyby\Doctrine\IQueryable, Kdyby\Doctrine\IObjectFactory
{

	/** @var EntityValuesMapper */
	private $entityMapper;



	/**
	 * @param array $values
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
				throw new Nette\InvalidArgumentException("EntityMapper service was not injected, therefore DAO cannot set values.");

			} else {
				$this->entityMapper->load($entity, $values);
			}
		}
		return $entity;
	}



	/**
	 * @param EntityValuesMapper $mapper
	 */
	public function setEntityMapper(EntityValuesMapper $mapper)
	{
		$this->entityMapper = $mapper;
	}



	/**
	 * @param object|array|Collection $entity
	 * @param boolean $withoutFlush
	 * @return object|array
	 */
	public function save($entity, $withoutFlush = self::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->save($entity->toArray(), $validate, $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			$result = array_map(function ($entity) use ($repository, $validate) {
				return $repository->save($entity, $validate, TRUE);
			}, $entity);

			if ($withoutFlush === FALSE) {
				$this->getEntityManager()->flush();
			}

			return $result;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->getEntityManager()->persist($entity);
		if ($withoutFlush === FALSE) {
			$this->getEntityManager()->flush();
		}

		return $entity;
	}



	/**
	 * @param object|array|Collection $entity
	 * @param boolean $withoutFlush
	 */
	public function delete($entity, $withoutFlush = self::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->delete($entity->toArray(), $withoutFlush);
		}

		if (is_array($entity)) {
			$repository = $this;
			array_map(function ($entity) use ($repository) {
				return $repository->delete($entity, TRUE);
			}, $entity);

			if ($withoutFlush === FALSE) {
				$this->getEntityManager()->flush();
			}
			return;
		}

		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->getEntityManager()->remove($entity);
		if ($withoutFlush === FALSE) {
			$this->getEntityManager()->flush();
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
	 * @param IQueryObject $queryObject
	 * @return integer
	 */
	public function count(IQueryObject $queryObject)
	{
		return $queryObject->count($this->getEntityManager()->createQueryBuilder());
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return array
	 */
	public function fetch(IQueryObject $queryObject)
	{
		return $queryObject->fetch($this);
	}



	/**
	 * @param IQueryObject $queryObject
	 * @return object
	 */
	public function fetchOne(IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetchOne($this);

		} catch (NoResultException $e) {
			return NULL;

		} catch (NonUniqueResultException $e) {
			return NULL;
		}
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
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