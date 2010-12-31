<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Doctrine;

use Nette;
use Nette\Environment;
use Nette\Caching\Cache AS NCache;



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
			NCache::TAGS => array_merge(array(get_class($this)), $this->getCacheTags())
		));
	}

}