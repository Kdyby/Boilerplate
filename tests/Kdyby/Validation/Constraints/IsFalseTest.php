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
class IsFalseTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsFalse */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\IsFalse();
	}



	public function testEvaluate()
	{
		$this->assertFalse($this->constraint->evaluate(NULL));
		$this->assertFalse($this->constraint->evaluate(''));
		$this->assertFalse($this->constraint->evaluate(1));
		$this->assertFalse($this->constraint->evaluate(1.1));
		$this->assertTrue($this->constraint->evaluate(FALSE));
		$this->assertFalse($this->constraint->evaluate(TRUE));
		$this->assertFalse($this->constraint->evaluate(array()));
		$this->assertFalse($this->constraint->evaluate((object)array()));
	}

}