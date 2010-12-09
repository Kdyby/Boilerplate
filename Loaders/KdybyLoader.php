<?php

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

	/** @var array */
	public $list = array(
		'kdyby\configurator' => '/Environment/Configurator.php',
		'kdyby\filesystem' => '/Tools/FileSystem.php',
		'kdyby\logicdelegator' => '/Tools/LogicDelegator.php',
		'kdyby\presenter\base' => '/Presenters/Base.php',
		'kdyby\presenterinfo' => '/Tools/PresenterTree/PresenterInfo.php',
		'kdyby\presentertree' => '/Tools/PresenterTree/PresenterTree.php',
		'kdyby\security\applicationlock' => '/Security/ApplicationLock.php',

		// doctrine
		'kdyby\application\databasemanager' => '/Doctrine/DatabaseManager.php',
		'kdyby\doctrine\cache' => '/Doctrine/Cache.php',
		'kdyby\doctrine\factory' => '/Doctrine/Factory.php',
		'kdyby\entities\baseentity' => '/Doctrine/BaseEntity.php',
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