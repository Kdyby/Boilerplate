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
class IsShorterThanTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsShorterThan */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\IsShorterThan(10);
	}



	/**
	 * @return array
	 */
	public function getLongerOrEquals10()
	{
		return array(
			array('AAAAAAAAAA'),
			array("AAAAAAAAAAAA"),
			array("AAAAAAAAAAAAAAA")
		);
	}



	/**
	 * @dataProvider getLongerOrEquals10
	 * @param integer $number
	 */
	public function testEvaluateLonger($number)
	{
		$this->assertFalse($this->constraint->evaluate($number));
	}



	/**
	 * @return array
	 */
	public function getShorterThan10()
	{
		return array(
			array('AAAAAAAAA'),
			array('AAAAAA'),
			array(''),
		);
	}



	/**
	 * @dataProvider getShorterThan10
	 * @param integer $number
	 */
	public function testEvaluateShorterOrEquals($number)
	{
		$this->assertTrue($this->constraint->evaluate($number));
	}

}