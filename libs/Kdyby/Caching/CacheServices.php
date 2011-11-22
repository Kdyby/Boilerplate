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
 * @property-read IStorage $cacheStorage
 * @property-read Storages\IJournal $cacheJournal
 * @property-read IStorage $phpFileStorage
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
		$this->tempDir = $tempDir;
	}



	/**
	 * @return IStorage
	 */
	protected function createServiceCacheStorage()
	{
		$dir = $this->tempDir . '/cache';
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Storages\FileStorage($dir, $this->cacheJournal);
	}



	/**
	 * @return IStorage
	 */
	protected function createServicePhpFileStorage()
	{
		$dir = $this->tempDir . '/cache';
		umask(0000);
		@mkdir($dir, 0777); // @ - directory may exists
		return new Storages\PhpFileStorage($dir);
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