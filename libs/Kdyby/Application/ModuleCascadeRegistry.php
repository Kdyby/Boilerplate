<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Utils\Arrays;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read \ArrayIterator $namespace
 * @property-read \ArrayIterator $directory
 */
class ModuleCascadeRegistry extends Nette\FreezableObject
{

	/** @var array */
	private $modules = array();



	/**
	 * @param string $namespace
	 * @param string $directory
	 * @param int|NULL $priority
	 * @return AppCascadeRegistry
	 */
	public function add($namespace, $directory)
	{
		$this->updating();
		$this->modules[$namespace] = $directory;
		return $this;
	}



	/**
	 * @param string $namespace
	 * @return string
	 */
	public function getNamespaceDirectory($namespace)
	{
		if (!isset($this->modules[$namespace])) {
			throw new Nette\InvalidArgumentException("Namespace " . $namespace . " is not registered.");
		}

		return realpath($this->modules[$namespace]);
	}



	/**
	 * @param string $directory
	 * @return string
	 */
	public function getDirectoryNamespace($directory)
	{
		if (!($namespace = array_search($directory, $this->modules))) {
			throw new Nette\InvalidArgumentException("Directory " . $directory . " is not registered.");
		}

		return $namespace;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getNamespaces()
	{
		return new \ArrayIterator(array_reverse(array_keys($this->modules)));
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getDirectories()
	{
		return new \ArrayIterator(array_reverse(array_values($this->modules)));
	}

}