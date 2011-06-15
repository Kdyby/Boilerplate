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
class LessThanTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\LessThan */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\LessThan(10);
	}



	/**
	 * @return array
	 */
	public function getGreaterOrEqual10()
	{
		return array(
			array(10),
			array(11),
			array(20),
		);
	}



	/**
	 * @dataProvider getGreaterOrEqual10
	 * @param integer $number
	 */
	public function testEvaluateGreaterOrEquals($number)
	{
		$this->assertFalse($this->constraint->evaluate($number));
	}



	/**
	 * @return array
	 */
	public function getLessThan10()
	{
		return array(
			array(9),
			array(0),
			array(-10)
		);
	}



	/**
	 * @dataProvider getLessThan10
	 * @param integer $number
	 */
	public function testEvaluateLess($number)
	{
		$this->assertTrue($this->constraint->evaluate($number));
	}

}