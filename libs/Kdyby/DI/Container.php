<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Container extends Nette\DI\Container
{

	public function getParam($key, $default = NULL)
	{
		if (isset($this->params[$key])) {
			return $this->params[$key];

		} elseif (func_num_args()>1) {
			return $default;
		}

		throw new Nette\OutOfRangeException("Missing key $key in " . get_class($this) . '->params');
	}

}