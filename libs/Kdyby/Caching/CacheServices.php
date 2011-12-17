<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Caching;

use Kdyby;
use Nette;
use Nette\Caching\IStorage;
use Nette\Caching\Storages;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \Nette\Caching\IStorage $cacheStorage
 * @property-read \Nette\Caching\Storages\IJournal $cacheJournal
 * @property-read \Nette\Caching\IStorage $phpFileStorage
 */
class CacheServices extends Nette\DI\Container
{

	/** @var string */
	private $tempDir;



	/**
	 * @param string $tempDir
	 */
	public function __construct($tempDir)
	{
		if (!is_dir($tempDir . '/cache')) {
			mkdir($tempDir . '/cache', 0777);
		}

		$this->tempDir = $tempDir;
	}



	/**
	 * @return IStorage
	 */
	protected function createServiceCacheStorage()
	{
		return new Storages\FileStorage($this->tempDir . '/cache', $this->cacheJournal);
	}



	/**
	 * @return IStorage
	 */
	protected function createServicePhpFileStorage()
	{
		return new Storages\PhpFileStorage($this->tempDir . '/cache', $this->cacheJournal);
	}



	/**
	 * @return Storages\IJournal
	 */
	protected function createServiceCacheJournal()
	{
		return new Storages\FileJournal($this->tempDir);
	}



	/**
	 * @param string $namespace
	 * @param boolean $usePhpFileStorage
	 * @return Nette\Caching\Cache
	 */
	public function create($namespace, $usePhpFileStorage = FALSE)
	{
		$storage = $usePhpFileStorage ? $this->phpFileStorage : $this->cacheStorage;
		return new Nette\Caching\Cache($storage, $namespace);
	}

}
