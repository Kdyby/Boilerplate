<?php

namespace Kdyby\ORM;

use Nette;
use Kdyby;
use ORM\IConfigurator;
use ORM\IIdentityMap;
use InvalidArgumentException;



class EntityIdentityMap extends Nette\Object implements IIdentityMap
{

	/** @var array */
	private $storage = array();



	/**
	 * @param int $id
	 * @param object $object
	 */
	public function add($id, $object)
	{
		$entityClass = get_class($object);

		if (isset($this->storage[$entityClass][$id])) {
			throw new InvalidArgumentException("Object '$entityClass' with id '$id' is already in map");

		} elseif (in_array($object, $this->storage[$entityClass], TRUE)) {
			throw new InvalidArgumentException();
		}

		$this->storage[$entityClass][$id] = $object;
	}



	/**
	 * @param int $id
	 * @return object|NULL
	 */
	public function get($id)
	{
		$entityClass = get_class($object);

		return isset($this->storage[$entityClass][$id]) ? $this->storage[$entityClass][$id] : NULL;
	}



	/**
	 * @param object $object
	 */
	public function remove($object)
	{
		$entityClass = get_class($object);

		unset($this->storage[$entityClass][$this->identify($object)]);
	}



	/**
	 * @param object $object
	 * @return int
	 */
	public function identify($object)
	{
		$entityClass = get_class($object);

		$id = array_search($object, $this->storage[$entityClass], TRUE);
		if ($id === FALSE) {
			throw new InvalidArgumentException('Unknown object');
		}
		return $id;
	}
}