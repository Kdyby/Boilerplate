<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tests\Constraint;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ObjectWithEventMock extends Nette\Object
{

	/** @var array */
	public $onEvent = array();



	public function foo()
	{
	}



	public static function staticFoo()
	{
	}



	public function __invoke()
	{
		return TRUE;
	}

}
