<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette;
use Nette\Reflection\ClassType;
use Nette\Caching\Cache AS NCache;



/**
 * Nette cache driver for doctrine
 *
 * @author Patrik Votoček
 * @author Filip Procházka
 */
class Cache extends Doctrine\Common\Cache\AbstractCache
{

	/** @var string */
	const CACHED_KEY = 'Doctrine';

	/** @var NCache */
	private $cache;

	/** @var string The namespace to prefix all cache ids with */
	private $namespace;



	/**
	 * @param Nette\Caching\IStorage $storage
	 */
	public function __construct(Nette\Caching\IStorage $storage)
	{
		$this->cache = new NCache($storage, self::CACHED_KEY);
	}



	/**
	 * Set the namespace to prefix all cache ids with.
	 *
	 * @param string $namespace
	 * @return void
	 */
	public function setNamespace($namespace)
	{
		$this->namespace = (string)$namespace;
		return parent::setNamespace($namespace);
	}



	/**
	 * Prefix the passed id with the configured namespace value
	 *
	 * @param string $id  The id to namespace
	 * @return string $id The namespaced id
	 */
	private function getNamespacedId($id)
	{
		if (!$this->namespace || strpos($id, $this->namespace) === 0) {
			return $id;
		}

		return $this->namespace . $id;
	}



	/**
	 * @return Nette\Caching\Cache
	 */
	private function getCache()
	{
		$this->cache->release();
		return $this->cache;
	}



	/**
	 * {@inheritdoc}
	 */
	public function saveDependingOnFiles($id, $data, array $files, $lifeTime = 0)
	{
		return $this->doSaveDependingOnFiles($this->getNamespacedId($id), $data, $files, $lifeTime);
	}



	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		return array();
	}



	/**
     * Delete all cache entries.
     *
     * @return array $deleted  Array of the deleted cache ids
     */
	public function deleteAll()
	{
		$this->getCache()->clean(array(NCache::TAGS => array('doctrine')));
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doFetch($id)
	{
		return $this->getCache()->load($id) ?: FALSE;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doContains($id)
	{
		return $this->getCache()->load($id) !== NULL;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doSave($id, $data, $lifeTime = 0)
	{
		$files = array();
		if ($data instanceof Doctrine\ORM\Mapping\ClassMetadata) {
			$files[] = ClassType::from($data->name)->getFileName();
			foreach ($data->parentClasses as $class) {
				$files[] = ClassType::from($class)->getFileName();
			}
		}

		return $this->doSaveDependingOnFiles($id, $data, $files, $lifeTime);
	}



	/**
	 * @param string $id
	 * @param mixed $data
	 * @param array $files
	 * @param integer $lifeTime
	 * @return boolean
	 */
	protected function doSaveDependingOnFiles($id, $data, array $files, $lifeTime = 0)
	{
		$dp = array(NCache::TAGS => array("doctrine"), NCache::FILES => $files);
		if ($lifeTime != 0) {
			$dp[NCache::EXPIRE] = time() + $lifeTime;
		}

		$this->getCache()->save($id, $data, $dp);
		return TRUE;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doDelete($id)
	{
		$this->getCache()->save($id, NULL);
		return TRUE;
	}

}