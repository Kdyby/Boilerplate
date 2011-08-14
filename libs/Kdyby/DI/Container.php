<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Symfony\Component\Console;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Container extends Nette\DI\Container
{

	/**
	 * @param string $key
	 * @param string|NULL $default
	 * @throws Nette\OutOfRangeException
	 * @return mixed
	 */
	public function getParam($key, $default = NULL)
	{
		if (isset($this->params[$key])) {
			return $this->params[$key];

		} elseif (func_num_args()>1) {
			return $default;
		}

		throw new Nette\OutOfRangeException("Missing key $key in " . get_class($this) . '->params');
	}



	/**
	 * @param string $name
	 * @param Nette\DI\IContainer $container
	 */
	public function lazyCopy($name, Nette\DI\IContainer $container)
	{
		$this->addService($name, function() use ($name, $container) {
			return $container->getService($name);
		});
	}

}