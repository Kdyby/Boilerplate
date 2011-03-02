<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Loaders;

use Nette;
use Nette\Loaders\LimitedScope;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
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