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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ExceptionsLoader extends Nette\Loaders\AutoLoader
{

	/**
	 * @param string $type
	 */
	public function tryLoad($type)
	{
		if (substr($type, -9) !== 'Exception') {
			return;
		}

		if (class_exists('Kdyby\CMS')) {
			foreach (Kdyby\CMS::findExceptionClasses() as $file) {
				require_once $file;
			}

		} else {
			foreach (Kdyby\Framework::findExceptionClasses() as $file) {
				require_once $file;
			}
		}
	}

}
