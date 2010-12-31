<?php

/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2010 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 */
/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette\Caching\Cache AS NCache;



/**
 * Nette cache driver for doctrine
 *
 * @author	Patrik Votoček
 * @author	Filip Procházka
 * @package	Kdyby\Doctrine
 */
class Cache extends Doctrine\Common\Cache\AbstractCache
{
	/** @var string */
	const CACHED_KEYS_KEY = 'Kdyby.Doctrine.Cache.Keys';

	/** @var array */
	private $data = array();

	/** @var array */
	private $keys = array();



	/**
	 * @param Nette\Caching\Cache
	 */
	public function  __construct(NCache $cache)
	{
		$this->data = $cache;
		$this->keys = $cache->derive('.Keys');
	}



	/**
	 * @param scalar $key
	 */
	private function removeCacheKey($key)
	{
		$keys = $this->keys[self::CACHED_KEYS_KEY];
		if (isset($keys[$key])) {
			unset($keys[$key]);
			$this->keys[self::CACHED_KEYS_KEY] = $keys;
		}

		return $keys;
	}



	/**
	 * @param scalar $key
	 */
	private function addCacheKey($key, $lifetime = 0)
	{
		$keys = $this->keys[self::CACHED_KEYS_KEY];
		if (!isset($keys[$key]) || $keys[$key] !== ($lifetime ?: TRUE)) {
			$keys[$key] = $lifetime ?: TRUE;
			$this->keys[self::CACHED_KEYS_KEY] = $keys;
		}

		return $keys;
	}



	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		$keys = (array)$this->keys[self::CACHED_KEYS_KEY];
		$keys = array_filter($keys, function($expire) {
			if ($expire > 0 && $expire < time()) {
				return FALSE;
			} // otherwise it's still valid

			return TRUE;
		});

		if ($keys !== $this->keys[self::CACHED_KEYS_KEY]) {
			$this->keys[self::CACHED_KEYS_KEY] = $keys;
		}

		return array_keys($keys);
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doFetch($id)
	{
		if (isset($this->data[$id])) {
			return $this->data[$id];
		}

		return FALSE;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doContains($id)
	{
		return isset($this->ids[$id]) && isset($this->data[$id]);
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doSave($id, $data, $lifeTime = 0)
	{
		if ($lifeTime != 0) {
			$this->addCacheKey($id, time() + $lifeTime);
			$this->data->save($id, $data, array('expire' => time() + $lifeTime, 'tags' => array("doctrine")));

		} else {
			$this->addCacheKey($id);
			$this->data->save($id, $data, array('tags' => array("doctrine")));
		}

		return TRUE;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doDelete($id)
	{
		$this->removeCacheKey($id);
		unset($this->data[$id]);
		return TRUE;
	}

}