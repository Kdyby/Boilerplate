<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
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
