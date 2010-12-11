<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
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
class DatabaseManager extends Nette\Object implements \ArrayAccess
{

	/** @var Doctrine\ORM\EntityManager */
	protected $entityManager;



	public function __construct()
	{
		$this->entityManager = Nette\Environment::getService('Doctrine\ORM\EntityManager');
	}



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->entityManager, $name), $arguments);
	}



	/**
	 * @param object $entity
	 */
	public function persist($entity)
	{
		$this->entityManager->persist($entity);
	}



	/**
	 * @param object $entity
	 * @param mixed $version
	 */
	public function lock($entity, $version)
	{
		$this->entityManager->lock($entity, Doctrine\DBAL\LockMode::OPTIMISTIC, $version);
	}





	/********************* \ArrayAccess *********************/



	/**
	 * @param string $offset
	 * @param string $value
	 * @throws NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new \NotSupportedException();
	}



	/**
	 * @param string $offset
	 * @throws NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new \NotSupportedException();
	}



	/**
	 * @param string $offset
	 * @return string
	 */
	public function offsetGet($offset)
	{
		return $this->entityManager->getRepository($offset);
	}



	/**
	 * @param string $offset
	 * @return boolean
	 * @throws NotSupportedException
	 */
	public function offsetExists($offset)
	{
		throw new \NotSupportedException();
	}
}