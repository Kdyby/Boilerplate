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
class TraversableContainsTest extends Kdyby\Testing\Test
{

	public function testEvaluateSplObjectStorageAsTraversable()
	{
		$obj1 = (object)array('key' => 'value');
		$obj2 = (object)array('key' => 'value');

		$constraint1 = new Kdyby\Validation\Constraints\TraversableContains($obj1);
		$constraint2 = new Kdyby\Validation\Constraints\TraversableContains($obj2);

		$traversable = new \SplObjectStorage();
		$traversable->attach($obj1);

		$this->assertTrue($constraint1->evaluate($traversable));
		$this->assertFalse($constraint2->evaluate($traversable));
	}



	public function testEvaluateObjectInTraversable()
	{
		$obj1 = (object)array('key' => 'value');
		$obj2 = (object)array('key' => 'value');

		$constraint1 = new Kdyby\Validation\Constraints\TraversableContains($obj1);
		$constraint2 = new Kdyby\Validation\Constraints\TraversableContains($obj2);

		$traversable = array($obj1);

		$this->assertTrue($constraint1->evaluate($traversable));
		$this->assertFalse($constraint2->evaluate($traversable));
	}



	public function testEvaluateArrayInTraversable()
	{
		$arr1 = array('key' => 'value1'); // pirate array?
		$arr2 = array('key' => 'value2');

		$constraint1 = new Kdyby\Validation\Constraints\TraversableContains($arr1);
		$constraint2 = new Kdyby\Validation\Constraints\TraversableContains($arr2);

		$traversable = array($arr1);

		$this->assertTrue($constraint1->evaluate($traversable));
		$this->assertFalse($constraint2->evaluate($traversable));
	}



	public function testEvaluateScalarInTraversable()
	{
		$scalar1 = 'value1';
		$scalar2 = 'value2';

		$constraint1 = new Kdyby\Validation\Constraints\TraversableContains($scalar1);
		$constraint2 = new Kdyby\Validation\Constraints\TraversableContains($scalar2);

		$traversable = array($scalar1);

		$this->assertTrue($constraint1->evaluate($traversable));
		$this->assertFalse($constraint2->evaluate($traversable));
	}

}