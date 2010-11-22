<?php

namespace Kdyby\Component;

use Nette;
use Nette\String;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Helpers extends Nette\Object
{

	public static function createTemplate()
	{
		
	}



	/**
	 * @param Nette\Application\PresenterComponent $_this
	 * @param Nette\Component|NULL $component
	 * @return Nette\Component
	 */
	public static function createComponent($_this, $component, $name)
	{
		if ($component !== Null) {
			return $component;
		}

		if ($m = String::match($name, "~^(?P<form>.+)Form$~")) {
			$ns = $_this->reflection->getNamespaceName();
			if (String::match($ns, "~^[^\\\\]+Module$~")) {
				$formClass = $ns . "\\Form\\" . ucfirst($m['form']);
				if (class_exists($formClass)) {
					return $component = new $formClass($_this, $name);
				}
			}

			$formClass = "\\Kdyby\\Form\\" . ucfirst($m['form']);
			if (class_exists($formClass)) {
				return $component = new $formClass($_this, $name);
			}
		}
	}


	

}
