<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation\Constraints;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class IsInstanceOfTest extends Kdyby\Testing\Test
{

	public function testEvaluate()
	{
		$obj1 = (object)array('key' => 'value');
		$obj2 = new \ArrayObject(array());

		$constraint = new Kdyby\Validation\Constraints\IsInstanceOf('stdClass');

		$this->assertTrue($constraint->evaluate($obj1));
		$this->assertFalse($constraint->evaluate($obj2));
	}

}