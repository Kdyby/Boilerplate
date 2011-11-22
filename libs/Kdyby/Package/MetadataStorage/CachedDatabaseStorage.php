<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\MetadataStorage;

use Kdyby;
use Kdyby\Package\PackageMeta;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CachedDatabaseStorage extends Nette\Object implements Kdyby\Package\IMetadataStorage
{

	const CACHE_NS = 'Kdyby.Packages.Meta';

	/** @var Cache */
	private $cache;

	/** @var DatabaseStorage */
	private $storage;



	/**
	 * @param DatabaseStorage $databaseStorage
	 * @param IStorage $storage
	 */
	public function __construct(DatabaseStorage $databaseStorage, IStorage $storage)
	{
		$this->storage = $databaseStorage;
		$this->cache = new Cache($storage, self::CACHE_NS);
	}



	/**
	 * @param PackageMeta $meta
	 */
	public function save(PackageMeta $meta)
	{
		$this->storage->save($meta);
	}



	/**
	 * @param string $packageName
	 * @return PackageMeta
	 */
	public function load($packageName)
	{
		if (!class_exists($packageName)) {
			throw new Nette\InvalidArgumentException("Package '" . $packageName . "' not found.");
		}

		$lName = strtolower($packageName);
		$meta = $this->cache->load($lName);
		if ($meta === NULL) {
			$meta = $this->storage->load($packageName);

			if ($meta instanceof PackageMeta) {
				$this->cache->save($lName, $meta);
			}
		}

		return $meta;
	}



	/**
	 * @param string $packageName
	 */
	public function remove($packageName)
	{
		$this->storage->remove($packageName);
		$this->cache->remove(strtolower($packageName));
	}

}