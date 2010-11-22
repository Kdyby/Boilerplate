<?php

namespace Kdyby\Loaders;

use Nette;
use Nette\Loaders\LimitedScope;



class KdybyLoader extends Nette\Loaders\AutoLoader
{
	/** @var KdybyLoader */
	private static $instance;

	/** @var array */
	public $list = array(
		'kdyby\configurator' => '/Application/Configurator.php',
		'kdyby\fileservice' => '/Tools/FileService.php',
		'kdyby\logicdelegator' => '/Tools/LogicDelegator.php',
		'kdyby\security\applicationlock' => '/Security/ApplicationLock.php'
	);



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
		$type = ltrim(strtolower($type), '\\');
		if (isset($this->list[$type])) {
			LimitedScope::load(KDYBY_DIR . $this->list[$type]);
			self::$count++;
		}
	}

}