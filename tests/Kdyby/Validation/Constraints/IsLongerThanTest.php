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
class IsLongerThanTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsLongerThan */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\IsLongerThan(10);
	}



	/**
	 * @return array
	 */
	public function getLongerThan10()
	{
		return array(
			array("AAAAAAAAAAAA"),
			array("AAAAAAAAAAAAAAA")
		);
	}



	/**
	 * @dataProvider getLongerThan10
	 * @param integer $number
	 */
	public function testEvaluateLonger($number)
	{
		$this->assertTrue($this->constraint->evaluate($number));
	}



	/**
	 * @return array
	 */
	public function getLongerOrEquals10()
	{
		return array(
			array('AAAAAAAAAA'),
			array('AAAAAAAAA'),
			array(''),
		);
	}



	/**
	 * @dataProvider getLongerOrEquals10
	 * @param integer $number
	 */
	public function testEvaluateLongerOrEquals($number)
	{
		$this->assertFalse($this->constraint->evaluate($number));
	}

}