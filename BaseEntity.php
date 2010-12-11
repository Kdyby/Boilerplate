<?php

namespace Kdyby\Entities;

use Nette;
use Nette\Environment;
use Nette\Caching\Cache;



/**
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 *
 * @property-read int $id
 * @author Jan Smitka
 */
abstract class BaseEntity extends Nette\Object
{

	/** @var Doctrine\ORM\EntityRepository */
	private $repository;



	public function __construct() { }



	public function persist()
	{
		Environment::getDatabaseManager()->persist($this);
	}



	public function remove()
	{
		Environment::getDatabaseManager()->remove($this);
	}



	/**
	 * @param array $data
	 */
	public function setValues(array $data)
	{
		foreach ($data as $key => $value) {
			$this->__set($key, $value);
		}
	}



	/**
	 * @return Doctrine\ORM\EntityRepository
	 */
	public function getRepository()
	{
		if ($this->repository === NULL) {
			$this->repository = Environment::getEntityManager()->getRepository(get_class($this));
		}
		return $this->repository;
	}



	public function free()
	{
		$this->repository = NULL;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		$this->free();

		$reflection = new \ReflectionClass($this);
		$properties = array_map(function($property) { return $property->name; }, $reflection->getProperties());

		return array_diff($properties, array(
				'repository', '_entityPersister', '_identifier', '__isInitialized__'
			));
	}



	/**
	 * @return array
	 */
	public function getCacheTags()
	{
		$tags = array();
		if ($this->id !== NULL) {
			$tags[] = get_class($this) . '#' . $this->id;
		}
		return $tags;
	}



	/**
	 * @PostPersist
	 * @PostUpdate
	 * @PostRemove
	 */
	public function cleanCache()
	{
		Environment::getCache()->clean(array(
			Cache::TAGS => array_merge(array(get_class($this)), $this->getCacheTags())
		));
	}

}