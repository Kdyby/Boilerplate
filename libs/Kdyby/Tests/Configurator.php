<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Configurator extends Kdyby\DI\Configurator
{

	/**
	 * @return string
	 */
	protected function getConfigFile()
	{
		return $this->params['appDir'] . '/config.neon';
	}

}