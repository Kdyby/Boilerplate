<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Loaders;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class SplClassLoaderMock extends Kdyby\Loaders\SplClassLoader
{

	/**
	 * @return Kdyby\Loaders\SplClassLoader
	 */
	public function getTestInstance($dirs)
	{
		return new Kdyby\Loaders\SplClassLoader($dirs);
	}

}