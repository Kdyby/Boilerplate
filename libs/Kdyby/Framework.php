<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @static
 */
final class Framework
{

	const NAME = 'Kdyby Framework';
	const VERSION = '2.0a';
	const REVISION = '$WCREV$ released on $WCDATE$';



	/**
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new StaticClassException;
	}

}
