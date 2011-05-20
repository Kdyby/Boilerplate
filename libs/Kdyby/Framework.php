<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby;

use Nette;



/**
 * @author Filip Procházka
 * @static
 */
final class Framework
{

	const NAME = 'Kdyby Framework';
	const VERSION = '8.1-dev';
	const REVISION = '$WCREV$ released on $WCDATE$';



	/**
	 * @throws Nette\StaticClassException
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}

}