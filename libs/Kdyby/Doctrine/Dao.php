<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\NonUniqueResultException;
use Kdyby;
use Kdyby\Persistence;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Dao extends Doctrine\ORM\EntityRepository implements Persistence\IDao, Persistence\IQueryExecutor, Persistence\IQueryable, Persistence\IObjectFactory
{

	/** @var \Kdyby\Doctrine\Mapping\ValuesMapper */
	private $entityMapper;



	/**
	 * @return \Kdyby\Doctrine\Mapping\ValuesMapper
	 */
	private function getEntityValuesMapper()
	{
		if ($this->entityMapper === NULL) {
			$this->entityMapper = new Mapping\ValuesMapper($this->_class, $this->_em);
		}

		return $this->entityMapper;
	}



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
			$this->getEntityValuesMapper()->load($entity, $values);
		}

		return $entity;
	}



	/**
	 * Persists given entities, but does not flush.
	 *
	 * @param object|array|\Doctrine\Common\Collections\Collection $entity
	 * @throws \Kdyby\InvalidArgumentException
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
	 * @param object|array|\Doctrine\Common\Collections\Collection $entity
	 * @return object|array
	 */
	public function save($entity = NULL)
	{
		if ($entity !== NULL) {
			$result = $this->add($entity);
			$this->flush();
			return $result;
		}

		$this->flush();
	}



	/**
	 * Fetches all records like $key => $value pairs
	 *
	 * @param array $criteria
	 * @param string $value
	 * @param string $key
	 *
	 * @return array
	 */
	public function findPairs($criteria, $value = NULL, $key = 'id')
	{
		if (!is_array($criteria)) {
			$key = $value ?: 'id';
			$value = $criteria;
			$criteria = array();
		}

		$builder = $this->createQueryBuilder('e')
			->select("e.$key, e.$value");

		foreach ($criteria as $k => $v) {
			$builder->andWhere('e.' . $k . ' = :prop' . $k)
				->setParameter('prop' . $k, $v);
		}
		$query = $builder->getQuery();

		try {
			$pairs = array();
			foreach ($res = $query->getResult(AbstractQuery::HYDRATE_ARRAY) as $row) {
				if (empty($row)) {
					continue;
				}

				$pairs[$row[$key]] = $row[$value];
			}

			return $pairs;

		} catch (\Exception $e) {
			return $this->handleException($e, $query);
		}
	}



	/**
	 * Fetches all records and returns an associative array indexed by key
	 *
	 * @param array $criteria
	 * @param string $key
	 *
	 * @return array
	 */
	public function findAssoc($criteria, $key = NULL)
	{
		if (!is_array($criteria)) {
			$key = $criteria;
			$criteria = array();
		}

		$query = $this->createQuery();
		try {
			$where = $params = array();
			foreach ($criteria as $k => $v) {
				$where[] = "e.$k = :prop$k";
				$params["prop$k"] = $v;
			}

			$where = $where ? 'WHERE ' . implode(' AND ', $where) : NULL;
			$query->setDQL('SELECT e FROM ' . $this->getEntityName() . " e INDEX BY e.$key $where");
			$query->setParameters($params);
			return $query->getResult();

		} catch (\Exception $e) {
			return $this->handleException($e, $query);
		}
	}



	/**
	 * @param object|array|\Doctrine\Common\Collections\Collection $entity
	 * @param bool $withoutFlush
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 * @return null
	 */
	public function delete($entity, $withoutFlush = Persistence\IDao::FLUSH)
	{
		if ($entity instanceof Collection) {
			return $this->delete($entity->toArray(), $withoutFlush);
		}

		if (is_array($entity)) {
			$dao = $this;
			array_map(function ($entity) use ($dao) {
				/** @var \Kdyby\Doctrine\Dao $dao */
				return $dao->delete($entity, Persistence\IDao::NO_FLUSH);
			}, $entity);

			$this->flush($withoutFlush);
			return NULL;
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
	protected function flush($withoutFlush = Persistence\IDao::FLUSH)
	{
		if ($withoutFlush === Persistence\IDao::FLUSH) {
			try {
				$this->getEntityManager()->flush();

			} catch (\PDOException $e) {
				$this->handleException($e);
			}
		}
	}



	/**
	 * @param string $alias
	 * @return \Kdyby\Doctrine\QueryBuilder $qb
	 */
	public function createQueryBuilder($alias = NULL)
	{
		$qb = new QueryBuilder($this->getEntityManager());

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
		$dql = implode(' ', func_get_args());
		return $this->getEntityManager()->createQuery($dql);
	}



	/**
	 * @param callable $callback
	 * @throws \Exception
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
	 * @param \Kdyby\Persistence\IQueryObject|\Kdyby\Doctrine\QueryObjectBase $queryObject
	 * @return integer
	 */
	public function count(Persistence\IQueryObject $queryObject)
	{
		try {
			return $queryObject->count($this);

		} catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
		}
	}



	/**
	 * @param \Kdyby\Persistence\IQueryObject|\Kdyby\Doctrine\QueryObjectBase $queryObject
	 * @return array|\Kdyby\Doctrine\ResultSet
	 */
	public function fetch(Persistence\IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetch($this);

		} catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
		}
	}



	/**
	 * @param \Kdyby\Persistence\IQueryObject|\Kdyby\Doctrine\QueryObjectBase $queryObject
	 *
	 * @throws \Kdyby\InvalidStateException
	 * @return object
	 */
	public function fetchOne(Persistence\IQueryObject $queryObject)
	{
		try {
			return $queryObject->fetchOne($this);

		} catch (NoResultException $e) {
			return NULL;

		} catch (NonUniqueResultException $e) { // this should never happen!
			throw new Kdyby\InvalidStateException("You have to setup your query calling ->setMaxResult(1).", 0, $e);

		} catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
		}
	}



	/**
	 * @param \Kdyby\Persistence\IQueryObject|\Kdyby\Doctrine\QueryObjectBase $queryObject
	 * @param string $key
	 * @param string $value
	 *
	 * @return array
	 */
	public function fetchPairs(Persistence\IQueryObject $queryObject, $key = NULL, $value = NULL)
	{
		try {
			$pairs = array();
			foreach ($queryObject->fetch($this, AbstractQuery::HYDRATE_ARRAY) as $row) {
				$offset = $key ? $row[$key] : reset($row);
				$pairs[$offset] = $value ? $value[$row] : next($row);
			}
			return array_filter($pairs); // todo: orly?

		} catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
		}
	}



	/**
	 * Fetches all records and returns an associative array indexed by key
	 *
	 * @param \Kdyby\Persistence\IQueryObject|\Kdyby\Doctrine\QueryObjectBase $queryObject
	 * @param string $key
	 *
	 * @throws \Exception
	 * @throws \Kdyby\InvalidStateException
	 * @return array
	 */
	public function fetchAssoc(Persistence\IQueryObject $queryObject, $key = NULL)
	{
		try {
			/** @var \Kdyby\Doctrine\ResultSet|mixed $resultSet */
			$resultSet = $queryObject->fetch($this);
			if (!$resultSet instanceof ResultSet || !($result = iterator_to_array($resultSet->getIterator()))) {
				return NULL;
			}

			try {
				$meta = $this->_em->getClassMetadata(get_class(current($result)));

			} catch (\Exception $e) {
				throw new Kdyby\InvalidStateException('Result of ' . get_class($queryObject) . ' is not list of entities.');
			}

			$assoc = array();
			foreach ($result as $item) {
				$assoc[$meta->getFieldValue($item, $key)] = $item;
			}
			return $assoc;

		} catch (Kdyby\InvalidStateException $e) {
			throw $e;

		} catch (\Exception $e) {
			return $this->handleQueryException($e, $queryObject);
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
	 * @param \Kdyby\Doctrine\QueryObjectBase $queryObject
	 *
	 * @throws \Exception
	 */
	private function handleQueryException(\Exception $e, QueryObjectBase $queryObject)
	{
		$this->handleException($e, $queryObject->getLastQuery(), '[' . get_class($queryObject) . '] ' . $e->getMessage());
	}



	/**
	 * @param \Exception $e
	 * @param \Doctrine\ORM\Query $query
	 * @param string $message
	 *
	 * @throws \Exception
	 * @throws \Kdyby\Doctrine\QueryException
	 * @throws \Kdyby\Doctrine\SqlException
	 */
	private function handleException(\Exception $e, Doctrine\ORM\Query $query = NULL, $message = NULL)
	{
		if ($e instanceof Doctrine\ORM\Query\QueryException) {
			throw new QueryException($e, $query, $message);

		} elseif ($e instanceof \PDOException) {
			throw new SqlException($e, $query, $message);

		} else {
			throw $e;
		}
	}



	/**
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	public function getClassMetadata()
	{
		return parent::getClassMetadata();
	}



	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return parent::getEntityManager();
	}



	/**
	 * @param string $relation
	 * @return \Kdyby\Doctrine\Dao
	 */
	public function related($relation)
	{
		$meta = $this->getClassMetadata();
		$targetClass = $meta->getAssociationTargetClass($relation);
		return $this->getEntityManager()->getRepository($targetClass);
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
