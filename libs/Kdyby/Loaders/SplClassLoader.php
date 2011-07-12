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



	/**
	 * @param array $namespaces
	 */
	protected function __construct(array $namespaces)
	{
		$this->namespaces = array_map(function ($namespace) {
			return trim($namespace, "\\");
		}, $namespaces);
	}



	/**
	 * @param string $namespace
	 * @param string $dir
	 */
	public function addAlias($namespace, $dir)
	{
		$this->namespaces[trim($namespace, "\\")] = $dir;
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
		$matching = $this->getFilteredByType($type);
		$namespace = $this->getLongestNamespace($matching);
		if ($namespace) {
			$type = substr($type, strlen($namespace)+1);
			$path = $this->namespaces[$namespace] . "/" . str_replace('\\', DIRECTORY_SEPARATOR, $type) . ".php";

			if (file_exists($path)) {
				Nette\Utils\LimitedScope::load($path);
			}
		}
	}



	/**
	 * @param string $type
	 * @return array
	 */
	public function getFilteredByType($type)
	{
		return array_filter(array_keys($this->namespaces), function ($namespace) use ($type) {
			return Strings::startsWith(strtolower($type), strtolower($namespace));
		});
	}



	/**
	 * @param array $namespaces
	 * @return string
	 */
	public function getLongestNamespace(array $namespaces)
	{
		usort($namespaces, function ($first, $second) {
			return substr_count($second, "\\") - substr_count($first, "\\");
		});

		return reset($namespaces);
	}
}
