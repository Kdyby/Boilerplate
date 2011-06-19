<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Modules;

use Kdyby;
use Nette;
use Nette\Caching\Cache;
use Nette\Iterators\Filter;



/**
 * @author Filip Procházka
 */
class InstallWizard extends Nette\Object
{

	const CACHE_NS = 'Kdyby.Modules.Installers';

	/** @var Nette\Loaders\RobotLoader */
	private $robotLoader;

	/** @var Cache */
	private $cache;



	/**
	 * @param Nette\Loaders\RobotLoader $loader
	 */
	public function __construct(Nette\Loaders\RobotLoader $loader, Nette\Caching\IStorage $storage)
	{
		$this->robotLoader = $loader;
		$this->cache = new Cache($storage, self::CACHE_NS);
	}



	/**
	 * @return array
	 */
	public function getInstallers()
	{
		$installersCreated = $this->cache->getStorage()->getCreateTime('installers');
		$classesIndexCreated = $this->robotLoader->getIndexCreateTime();

		if (!$this->cache->load('installers') && $installersCreated > $classesIndexCreated) {
			$this->cache->save('installers', callback($this, 'doSearchInstallers'));
		}

		return $this->cache->load('installers');
	}



	/**
	 * @return array
	 */
	public function doSearchInstallers()
	{
		$classes = new \ArrayIterator($this->robotLoader->getIndexedClasses());
		$installers = new Filter($classes, function (Filter $iterator) {
			$classRef = Nette\Reflection\ClassType::from($iterator->getInnerIterator()->key());
			return in_array('Kdyby\Modules\IInstaller', $classRef->getInterfaceNames());
		});

		return array_keys(iterator_to_array($installers));
	}

}