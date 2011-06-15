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
class StringContainsTest extends Kdyby\Testing\Test
{

	public function testEvaluateCaseInsensitive()
	{
		$constraint = new Kdyby\Validation\Constraints\StringContains('abc', TRUE);

		$this->assertTrue($constraint->evaluate('aaBCc'));
		$this->assertTrue($constraint->evaluate('aBcc'));
		$this->assertTrue($constraint->evaluate('AAbc'));

		$this->assertTrue($constraint->evaluate('aabcc'));
		$this->assertTrue($constraint->evaluate('abcc'));
		$this->assertTrue($constraint->evaluate('aabc'));
	}



	public function testEvaluateCaseSensitive()
	{
		$constraint = new Kdyby\Validation\Constraints\StringContains('abc');

		$this->assertFalse($constraint->evaluate('aaBCc'));
		$this->assertFalse($constraint->evaluate('aBcc'));
		$this->assertFalse($constraint->evaluate('AAbc'));

		$this->assertTrue($constraint->evaluate('aabcc'));
		$this->assertTrue($constraint->evaluate('abcc'));
		$this->assertTrue($constraint->evaluate('aabc'));
	}

}