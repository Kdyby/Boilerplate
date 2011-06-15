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
class ArrayHasKeyTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\ArrayHasKey */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\ArrayHasKey('key');
	}



	public function testEvaluate()
	{
		$this->assertTrue($this->constraint->evaluate(array(
			'key' => 'value'
		)));

		$this->assertFalse($this->constraint->evaluate(array(
			'key2' => 'value'
		)));
	}

}