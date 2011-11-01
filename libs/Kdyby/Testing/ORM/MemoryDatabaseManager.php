<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\ORM;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class MemoryDatabaseManager extends Nette\Object
{

	/** @var Nette\DI\Container */
	protected $context;

	/** @var Doctrine\Common\Cache\AbstractCache */
	private $cache;

	/** @var array */
	protected $register = array();



	/**
	 * @param Nette\DI\Container $context
	 */
	public function __construct(Nette\DI\Container $context)
	{
		$this->context = $context;
		$this->cache = new Kdyby\Doctrine\Cache(new Nette\Caching\Storages\MemoryStorage);
	}



	/**
	 * @param array $entities
	 * @return Sandbox
	 */
	public function refresh(array $entities = NULL)
	{
		$recycler = $this->getRecycler($entities);
		$recycler->refresh();
		return $recycler->getSandbox();
	}



	/**
	 * @param array $entities
	 * @return SandboxRecycler
	 */
	private function getRecycler(array $entities = NULL)
	{
		$entities = array_map(function ($class) {
			return trim($class, '\\');
		}, (array)$entities);
		sort($entities);

		$key = serialize($entities);
		if (isset($this->register[$key])) {
			return $this->register[$key];
		}
		return $this->register[$key] = new SandboxRecycler($this->doCreateSandboxRecycler($entities));
	}



	/**
	 * @param array $entities
	 * @return ISandboxBuilder
	 */
	protected function doCreateSandboxRecycler(array $entities)
	{
		$builder = new SandboxBuilder($this->cache);

		$builder->params['driver'] = 'pdo_sqlite';
		$builder->params['memory'] = TRUE;

		if ($entities) {
			$builder->params['entityNames'] = $entities;
		}

		$builder->expandParams($this->context);
		return $builder;
	}


}