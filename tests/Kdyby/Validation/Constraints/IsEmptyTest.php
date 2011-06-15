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
class IsEmptyTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsEmpty */
	private $constraint;



	public function setUp()
	{
		$this->constraint = new Kdyby\Validation\Constraints\IsEmpty();
	}



	/**
	 * @return array
	 */
	public function getEmptyValues()
	{
		return array(
			array(""),
			array(0),
			array(),
		);
	}



	/**
	 * @dataProvider getEmptyValues
	 * @param string $value
	 */
	public function testEvaluateValid($value)
	{
		$this->assertTrue($this->constraint->evaluate($value));
	}



	/**
	 * @return array
	 */
	public function getNotEmptyValues()
	{
		return array(
			array("ne"),
		);
	}



	/**
	 * @dataProvider getNotEmptyValues
	 * @param string $value
	 */
	public function testEvaluateInvalid($value)
	{
		$this->assertFalse($this->constraint->evaluate($value));
	}

}