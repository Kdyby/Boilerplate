<?php

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