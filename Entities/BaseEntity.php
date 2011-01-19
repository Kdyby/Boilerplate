<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
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