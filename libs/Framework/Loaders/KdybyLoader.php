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

	/** @var array */
	public $list = array(

		// doctrine
		'kdyby\doctrine\cache' => '/Doctrine/Cache.php',
		'kdyby\doctrine\factory' => '/Doctrine/Factory.php',
		'kdyby\doctrine\baseentity' => '/Doctrine/Entities/BaseEntity.php',
		'kdyby\doctrine\IdentifiedEntity' => '/Doctrine/Entities/IdentifiedEntity.php',
		'kdyby\doctrine\NamedEntity' => '/Doctrine/Entities/NamedEntityy.php',
		'kdyby\doctrine\service' => '/Doctrine/Services/Service.php',
		'kdyby\doctrine\entityservice' => '/Doctrine/Services/EntityService.php',
		'kdyby\doctrine\serviceexception' => '/Doctrine/Services/ServiceException.php',
		'nella\doctrine\panel' => '/Doctrine/NellaPanel/Panel.php',
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
		if ('\\' === $type[0]) {
			$type = substr($type, 1);
		}

		// indexed class
		$typeLower = strtolower($type);
		if (isset($this->list[$typeLower])) {
			LimitedScope::load(KDYBY_FRAMEWORK_DIR . $this->list[$typeLower]);
			return self::$count++;
		}

		// standartized namespaced class name
		if (FALSE !== ($pos = strripos($type, '\\')) && strtolower(substr($type, 0, 5)) === 'kdyby') {
			$namespace = substr($type, 0, $pos);
			$class = substr($type, $pos + 1);

			$file = KDYBY_FRAMEWORK_DIR . '/' . str_replace('\\', '/', $namespace) . '/' . $class . '.php';

			if (file_exists($file)) {
				LimitedScope::load($file);
				return self::$count++;
			}
		}
	}

}