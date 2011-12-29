<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
final class DefaultHelpers extends Nette\Object
{

	/**
	 * @throws \Kdyby\StaticClassException
	 */
	public function __construct()
	{
		throw new Kdyby\StaticClassException;
	}



	/**
	 * Try to load the requested helper.
	 *
	 * @param  string  helper name
	 *
	 * @return callback
	 */
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return callback(__CLASS__, $helper);
		}
	}

}
