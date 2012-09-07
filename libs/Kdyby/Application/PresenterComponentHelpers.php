<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PresenterComponentHelpers extends Nette\Object
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Kdyby\StaticClassException("Cannot instantiate static class " . get_class($this));
	}



	/******************** Links ************************/



	/**
	 * @param Nette\Application\UI\PresenterComponent $component
	 * @return array
	 */
	public static function nullLinkParams(Nette\Application\UI\PresenterComponent $component)
	{
		$parent = $component;
		$presenter = $component instanceof Nette\Application\UI\Presenter ? NULL : $component->lookup('Nette\Application\UI\Presenter');
		$params = array();

		do {
			if ($parent && method_exists($parent, 'getPersistentParams')) {
				$name = $parent instanceof Nette\Application\UI\Presenter ? '' : $parent->lookupPath(get_class($presenter));

				foreach ($parent->reflection->getPersistentParams() as $param => $info) {
					$params[($name ? $name . $component::NAME_SEPARATOR : NULL) . $param] = $info['def'] ?: NULL;
				}
			}

		} while($parent && $parent = $parent->getParent());

		return $params;
	}

}
