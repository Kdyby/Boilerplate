<?php

namespace Kdyby\Doctrine;

use Doctrine;
use Nette;
use Kdyby;



/**
 * @property-read Doctrine\ORM\EntityManager $entityManager
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class Service extends Nette\Object
{

	/** @var Kdyby\Application\DatabaseManager */
	private $databaseManager;



	/**
	 * @param Kdyby\Application\DatabaseManager $databaseManager
	 */
	public function setDatabaseManager(Kdyby\Application\DatabaseManager $databaseManager)
	{
		$this->databaseManager = $databaseManager;
	}



	/**
	 * @return Kdyby\Application\DatabaseManager
	 */
	public function getDatabaseManager()
	{
		return $this->databaseManager;
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->getDatabaseManager()->getEntityManager();
	}



	/**
	 * Update entity and flush
	 * @param Kdyby\Doctrine\BaseEntity entity
	 */
	public function update($entity)
	{
		return $this->save($entity);
	}



	/**
	 * Persist entity and flush
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @return Kdyby\Doctrine\BaseEntity
	 */
	public function save($entity)
	{
		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush();
		return $entity;
	}



	/**
	 * Delete entity and flush
	 * @param Kdyby\Doctrine\BaseEntity entity
	 */
	public function delete($entity)
	{
		$this->getEntityManager()->remove($entity);
		$this->getEntityManager()->flush();
		return $entity;
	}



	/**
	 * @param string $entityName
	 * @param array $data
	 */
	public function simpleBulkInsert($entityName, array $data)
	{
		$entityName = $this->getEntityFullName($entityName);

		foreach ($data as $item) {
			$entity = new $entityName;

			foreach ($item as $property => $value) {
				$entity->$property = $value;
			}

			$this->getEntityManager()->persist($entity);
		}

		$this->getEntityManager()->flush();
	}



	/**
	 * @param string $entityName
	 * @param string $key
	 * @param string $title
	 * @return array|NULL
	 */
	public function getPairs($entityName, $key = 'id')
	{
		// TODO: rename!

		$repository = $this->getEntityManager()->getRepository($this->getEntityFullName($entityName));

		$pairs = array();
		foreach ($repository->findAll() as $entity) {
			$pairs[$entity->{$key}] = $entity;
		}

		return $pairs ?: NULL;
	}



	/**
	 * @param string $entityName
	 * @return mixed
	 */
	public function trunctate($entityName)
	{
		return $this->getEntityManager()
			->getRepository($this->getEntityFullName($entityName))
			->createQueryBuilder('e')
			->delete()
			->getQuery()->getResult();
	}



	/**
	 * @param string $entityName
	 * @return string
	 */
	protected function getEntityFullName($entityName)
	{
		return substr_count($entityName, '\\') ? $entityName : $this->reflection->getNamespaceName() . '\\' . $entityName;
	}



	/**
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->getEntityManager(), $method), $args);
	}

}