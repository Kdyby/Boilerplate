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
	const CACHED_KEYS_KEY = 'Kdyby.Doctrine.Cache.Keys';

	/** @var NCache */
	private $data;

	/** @var NCache */
	private $keys;



	/**
	 * @param Nette\Caching\IStorage $storage
	 */
	public function __construct(Nette\Caching\IStorage $storage)
	{
		$this->data = $cache = new NCache($storage, "Kdyby.Doctrine");
		$this->keys = $cache->derive('Keys.List');
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
		$keys = $this->keys->load(self::CACHED_KEYS_KEY);
		if (!isset($keys[$key]) || $keys[$key] !== ($lifetime ?: TRUE)) {
			$keys[$key] = $lifetime ?: TRUE;
			$this->keys->save(self::CACHED_KEYS_KEY, $keys);
		}

		return $keys;
	}



	/**
	 * {@inheritdoc}
	 */
	public function getIds()
	{
		$keys = (array)$this->keys->load(self::CACHED_KEYS_KEY);
		$keys = array_filter($keys, function($expire) {
			if ($expire > 0 && $expire < time()) {
				return FALSE;
			} // otherwise it's still valid

			return TRUE;
		});

		if ($keys !== $this->keys->load(self::CACHED_KEYS_KEY)) {
			$this->keys->save(self::CACHED_KEYS_KEY, $keys);
		}

		return array_keys($keys);
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doFetch($id)
	{
		return $this->data->load($id) ?: FALSE;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doContains($id)
	{
		return $this->ids->load($id) !== NULL && $this->data->load($id) !== NULL;
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

		if ($lifeTime != 0) {
			$this->data->save($id, $data, array('expire' => time() + $lifeTime, 'tags' => array("doctrine"), 'files' => $files));
			$this->addCacheKey($id, time() + $lifeTime);

		} else {
			$this->data->save($id, $data, array('tags' => array("doctrine"), 'files' => $files));
			$this->addCacheKey($id);
		}

		return TRUE;
	}



	/**
	 * {@inheritdoc}
	 */
	protected function _doDelete($id)
	{
		unset($this->data[$id]);
		$this->removeCacheKey($id);
		return TRUE;
	}

}