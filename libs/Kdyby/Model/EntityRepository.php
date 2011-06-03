<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Model;

use Doctrine;
use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka
 */
class EntityRepository extends Doctrine\ORM\EntityRepository implements Kdyby\Validation\IStorage
{

	/**
	 * @param object $entity
	 */
	public function save($entity)
	{
		if (!$entity instanceof $this->_entityName) {
			throw new Nette\InvalidArgumentException("Entity is not instanceof " . $this->_entityName . ', ' . get_class($entity) . ' given.');
		}

		$this->_em->persist($entity);
		$this->_em->flush();
	}



	/**
	 * Create a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string $alias
	 * @return QueryBuilder $qb
	 */
	public function createQueryBuilder($alias)
	{
		return $this->doCreateQueryBuilder()->select($alias)
			->from($this->_entityName, $alias);
	}



	/**
	 * @return QueryBuilder
	 */
	protected function doCreateQueryBuilder()
	{
		return new QueryBuilder($this->getEntityManager());
	}



	/********************* Kdyby\Validation\IStorage *********************/



	/**
	 * @param string $attribute
	 * @param mixed $value
	 * @return int
	 */
	public function countByAttribute($attribute, $value)
	{
		$qb = $this->createQueryBuilder('e')
			->select('count(e) fullcount')
			->where('e.' . $attribute . ' = :value')
			->setParameter('value', $value);

		try {
			return (int)$qb->getQuery()->getSingleResult(Query::HYDRATE_SINGLE_SCALAR);

		} catch (Doctrine\ORM\ORMException $e) {
			throw new Kdyby\Doctrine\QueryException($e->getMessage(), $this->qb->getQuery(), $e);
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