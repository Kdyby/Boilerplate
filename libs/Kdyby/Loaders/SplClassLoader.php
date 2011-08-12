<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Loaders;

use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka
 */
class SplClassLoader extends Nette\Loaders\AutoLoader
{

	/** @var SplClassLoader */
	private static $instance;

	/** @var array */
	private $namespaces = array();

	/** @var array */
	private $dirs = array();



	/**
	 * @param array $namespaces
	 */
	protected function __construct(array $namespaces)
	{
		$this->addNamespaces($namespaces);
	}



	/**
	 * @param string $namespace
	 * @param string $dir
	 * @return SplClassLoader
	 */
	public function addNamespace($namespace, $dir)
	{
		$this->namespaces[] = trim($namespace, "\\");
		$this->dirs[] = $dir;
		return $this;
	}



	/**
	 * @param array $namespaces
	 * @return SplClassLoader
	 */
	public function addNamespaces(array $namespaces)
	{
		foreach ($namespaces as $namespace => $dir) {
			$this->addNamespace($namespace, $dir);
		}

		return $this;
	}



	/**
	 * @param array
	 * @return SplClassLoader
	 */
	public static function getInstance(array $map = array())
	{
		if (self::$instance === NULL) {
			self::$instance = new self($map);
		}
		return self::$instance;
	}



	/**
	 * @param string $type
	 * @return void
	 */
	public function tryLoad($type)
	{
		foreach ($this->getFilteredByType($type) as $i => $namespace) {
			$path = $this->dirs[$i] . "/" .
				str_replace('\\', DIRECTORY_SEPARATOR, substr($type, strlen($namespace)+1)) .
				".php";

			if (file_exists($path)) {
				Nette\Utils\LimitedScope::load($path);
				if (class_exists($type, FALSE)) {
					break;
				}
			}
		}
	}



	/**
	 * @param string $type
	 * @return array
	 */
	protected function getFilteredByType($type)
	{
		$namespaces = array_filter($this->namespaces, function ($namespace) use ($type) {
			return Strings::startsWith(strtolower($type), strtolower($namespace));
		});

		return $this->sortNamespacesByIndentation($namespaces);
	}



	/**
	 * @param array $namespaces
	 * @return array
	 */
	protected function sortNamespacesByIndentation(array $namespaces)
	{
		uasort($namespaces, function ($first, $second) {
			return substr_count($second, "\\") - substr_count($first, "\\");
		});

		return $namespaces;
	}
}
