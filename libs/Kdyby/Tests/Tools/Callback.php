<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Callback extends Nette\Object
{

	/**
	 * @return mixed
	 */
	public function __invoke()
	{
		$args = func_get_args();
		return callback($this, 'invoke')->invokeArgs($args);
	}

}
