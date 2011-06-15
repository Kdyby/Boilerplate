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
class GreaterThanTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\GreaterThan */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\GreaterThan(10);
	}



	/**
	 * @return array
	 */
	public function getGreaterThan10()
	{
		return array(
			array(11),
			array(20),
			array(1000)
		);
	}



	/**
	 * @dataProvider getGreaterThan10
	 * @param integer $number
	 */
	public function testEvaluateGreater($number)
	{
		$this->assertTrue($this->constraint->evaluate($number));
	}



	/**
	 * @return array
	 */
	public function getEqualOrEquals10()
	{
		return array(
			array(10),
			array(9),
			array(0),
			array(-10)
		);
	}



	/**
	 * @dataProvider getEqualOrEquals10
	 * @param integer $number
	 */
	public function testEvaluateEqualOrEquals($number)
	{
		$this->assertFalse($this->constraint->evaluate($number));
	}

}