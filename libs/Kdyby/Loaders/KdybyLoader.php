<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Loaders;

use Nette;
use Nette\Utils\LimitedScope;



/**
 * @author Filip Procházka
 */
class KdybyLoader extends Nette\Loaders\AutoLoader
{
	/** @var KdybyLoader */
	private static $instance;



	/**
	 * Returns singleton instance with lazy instantiation.
	 * @return KdybyLoader
	 */
	public static function getInstance()
	{
		if (self::$instance === NULL) {
			self::$instance = new self;
		}
		return self::$instance;
	}



	/**
	 * Handles autoloading of classes or interfaces.
	 * @param  string
	 * @return void
	 */
	public function tryLoad($type)
	{
		if ('\\' === $type[0]) {
			$type = substr($type, 1);
		}

		$namespace = substr(__NAMESPACE__, 0, strrpos(__NAMESPACE__, '\\'));

		if (strpos($type, $namespace . '\\') !== 0) {
			return FALSE;
		}

		$file = str_replace('\\', DIRECTORY_SEPARATOR, str_replace($namespace . '\\', KDYBY_DIR . DIRECTORY_SEPARATOR, $type)) . '.php';

		if (file_exists($file)) {
			LimitedScope::load($file);
			self::$count++;
		}
	}

}