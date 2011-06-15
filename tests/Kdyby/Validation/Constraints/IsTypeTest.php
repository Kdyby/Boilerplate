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
class IsTypeTest extends Kdyby\Testing\Test
{

	/** @var resource */
	private $resource;



	public function setUp()
	{
		$this->resource = fopen(__FILE__, 'r');
	}



	public function tearDown()
	{
		@fclose($this->resource);
	}



	public function testEvaluateArray()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('array');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertTrue($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateBoolean()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('boolean');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertTrue($constraint->evaluate(FALSE));
		$this->assertTrue($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateFloat()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('float');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertTrue($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateDouble()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('double');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertTrue($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateInteger()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('integer');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertTrue($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateNull()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('null');

		$this->assertTrue($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateNumeric()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('numeric');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertTrue($constraint->evaluate(1));
		$this->assertTrue($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateObject()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('object');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertTrue($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateResource()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('resource');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertFalse($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertTrue($constraint->evaluate($this->resource));
	}



	public function testEvaluateString()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('string');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertTrue($constraint->evaluate(''));
		$this->assertFalse($constraint->evaluate(1));
		$this->assertFalse($constraint->evaluate(1.1));
		$this->assertFalse($constraint->evaluate(FALSE));
		$this->assertFalse($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}



	public function testEvaluateScalar()
	{
		$constraint = new Kdyby\Validation\Constraints\IsType('scalar');

		$this->assertFalse($constraint->evaluate(NULL));
		$this->assertTrue($constraint->evaluate(''));
		$this->assertTrue($constraint->evaluate(1));
		$this->assertTrue($constraint->evaluate(1.1));
		$this->assertTrue($constraint->evaluate(FALSE));
		$this->assertTrue($constraint->evaluate(TRUE));
		$this->assertFalse($constraint->evaluate(array()));
		$this->assertFalse($constraint->evaluate((object)array()));
		$this->assertFalse($constraint->evaluate($this->resource));
	}

}