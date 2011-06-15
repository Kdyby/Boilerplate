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
class IsAnythingTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsAnything */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\IsAnything();
	}



	public function testEvaluate()
	{
		$this->assertTrue($this->constraint->evaluate(10000));
		$this->assertTrue($this->constraint->evaluate('anything'));
		$this->assertTrue($this->constraint->evaluate(array()));
		$this->assertTrue($this->constraint->evaluate((object)array()));
	}

}