<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Entities;

use Nette;
use Nette\Environment;



/**
 * @author Filip Procházka
 * @author Jan Smitka
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 *
 * @property-read int $id
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
			$this->$key = $value;
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
			Nette\Caching\Cache::TAGS => array_merge(array(get_class($this)), $this->getCacheTags())
		));
	}

}