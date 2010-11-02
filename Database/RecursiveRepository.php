<?php

namespace Kdyby\Database;



/**
 * Description of EntityRepository
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class RecursiveRepository extends Repository
{
	/** @var \Kdyby\Database\EntityRepository */
	private $parent;

	/** @var array  storage for shared objects */
	private $registry = array();



	/**
	 * @param  EntityRepository
	 */
	public function __construct(EntityRepository $parent = NULL)
	{
		$this->parent = $parent;
	}



	/**
	 * @param string $class
	 * @return string
	 */
	public function formatRepositoryClass($class)
	{
		return 'Kdyby\\Repository\\' . $class;
	}



	/**
	 * @param  string 
	 * @param  mixed  object
	 * @return void
	 */
	private function addRepository($name, $entity)
	{
		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Repository name must be a non-empty string, " . gettype($name) . " given.");
		}

		$lower = strtolower($name);
		if (isset($this->registry[$lower])) {
			throw new AmbiguousRepositoryException("Repository named '$name' has been already registered.");
		}

		if (is_object($entity)) {
			return $this->registry[$lower] = $entity;

		} else {
			throw new \InvalidArgumentException("Repository named '$name' is not object.");
		}
	}



	/**
	 * @param  string entity name
	 * @return mixed
	 */
	public function getRepository($name)
	{
		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Repository name must be a non-empty string, " . gettype($name) . " given.");
		}

		$lower = strtolower($name);

		if (isset($this->registry[$lower])) {
			return $this->registry[$lower];

		} else {
			$entity = $this->formatRepositoryClass($name);
			if (class_exists($entity)) {
				$instance = new $entity($this);
				if ($instance instanceof EntityRepository) {
					$this->addRepository($name, $instance);

				} else {
					return NULL;
				}
			}
		}

		if ($this->parent !== NULL) {
			return $this->parent->getRepository($name, $options);

		} else {
			throw new \InvalidStateException("Service '$name' not found.");
		}
	}



	/**
	 * Exists the service?
	 * @param  string entity name
	 * @return bool
	 */
	private function hasRepository($name)
	{
		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Entity name must be a non-empty string, " . gettype($name) . " given.");
		}

		return class_exists($this->formatEntityClass($name));
	}



	/**
	 * Returns the parent container if any.
	 * @return IServiceLocator|NULL
	 */
	public function getParent()
	{
		return $this->parent;
	}



	/********************* interface \ArrayAccess *********************/



	/**
	 * @param string $name
	 * @param object $entity
	 * @throws \NotImplementedException
	 */
	final public function offsetSet($name, $entity)
	{
		throw new \NotImplementedException("You cannot add a new entity manualy!");
	}



	/**
	 * @param string $name
	 * @return \Kdyby\Database\EntityBin
	 */
	final public function offsetGet($name)
	{
		return $this->getEntity($name);
	}



	/**
	 *
	 * @param string $name
	 * @return boolean
	 */
	final public function offsetExists($name)
	{
		return $this->hasRepository($name);
	}



	/**
	 * @param string $name
	 * @throws \NotImplementedException
	 */
	final public function offsetUnset($name)
	{
		throw new \NotImplementedException("There is no reason to clean up");
	}

}



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class AmbiguousRepositoryException extends \Exception
{ 
	
}