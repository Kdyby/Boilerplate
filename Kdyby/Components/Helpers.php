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


namespace Kdyby\Components;

use Nette;
use Nette\String;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Helpers extends Nette\Object
{

	static $namespacePrefixes = array(
		'\\Kdyby'
	);



	/**
	 * Magicaly searches for Component and if there is one,
	 * creates, attaches and returns it
	 *
	 * @param Nette\Application\PresenterComponent $_this
	 * @param Nette\Component|NULL $component
	 * @param string $name
	 * @param string|array $namespacePrefix
	 * @return Nette\Component
	 */
	public static function createComponent($_this, $component, $name)
	{
		if ($component !== Null) {
			return $component;
		}

		if ($m = String::match($name, '~^(?P<form>.+)Form$~')) {
			$ns = $_this->reflection->getNamespaceName();
			if (String::match($ns, '~^[^\\\\]+Module$~')) {
				$formClass = $ns . '\\Form\\' . ucfirst($m['form']);
				if (class_exists($formClass)) {
					return $component = new $formClass($_this, $name);
				}
			}

			foreach (self::$namespacePrefixes as $namespacePrefix) {
				$formClass = $namespacePrefix . '\\Form\\' . ucfirst($m['form']);
				if (class_exists($formClass)) {
					return $component = new $formClass($_this, $name);
				}
			}
		}
	}


	

}
