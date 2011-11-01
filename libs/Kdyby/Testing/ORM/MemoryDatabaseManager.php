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
use Kdyby\Doctrine\Sandbox;
use Kdyby\Doctrine\SandboxBuilder;
use Nette;



/**
 * @author Filip Procházka
 */
class MemoryDatabaseManager extends Nette\Object
{

	/** @var Nette\DI\Container */
	protected $context;

	/** @var array */
	protected $register = array();



	/**
	 * @param Nette\DI\Container $context
	 */
	public function __construct(Nette\DI\Container $context)
	{
		$this->context = $context;
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
		return $this->register[$key] = new SandboxRecycler($this->context, $entities);
	}


}