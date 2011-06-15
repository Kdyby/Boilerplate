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
class TraversableContainsOnlyTest extends Kdyby\Testing\Test
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



	public function testEvaluateInstanceStdClass()
	{
		$obj1 = (object)array();
		$obj2 = (object)array();
		$obj3 = (object)array();

		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('stdClass', FALSE);

		$this->assertTrue($constraint->evaluate(array($obj1)));
		$this->assertTrue($constraint->evaluate(array($obj1, $obj2, $obj3)));
		$this->assertFalse($constraint->evaluate(array('obj')));
		$this->assertFalse($constraint->evaluate(array($obj1, $obj2, 'obj')));
	}



	public function testEvaluateNativeNull()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('null', TRUE);

		$this->assertTrue($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeString()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('string', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertTrue($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeBoolean()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('boolean', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertTrue($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertTrue($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeFloat()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('float', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertTrue($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeDouble()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('double', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertTrue($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeInteger()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('integer', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertTrue($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeArray()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('array', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertTrue($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeNumeric()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('numeric', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertTrue($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertTrue($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeObject()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('object', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertTrue($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeResource()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('resource', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertFalse($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertFalse($constraint->evaluate(array(1, 2)));
		$this->assertFalse($constraint->evaluate(array(1, 'a')));

		$this->assertFalse($constraint->evaluate(array(1.2, 1.3)));
		$this->assertFalse($constraint->evaluate(array(1.2, 'a')));

		$this->assertFalse($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertFalse($constraint->evaluate(array(FALSE, 'a')));

		$this->assertFalse($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertFalse($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertTrue($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}



	public function testEvaluateNativeScalar()
	{
		$constraint = new Kdyby\Validation\Constraints\TraversableContainsOnly('scalar', TRUE);

		$this->assertFalse($constraint->evaluate(array(NULL, NULL)));
		$this->assertFalse($constraint->evaluate(array(NULL, '')));

		$this->assertTrue($constraint->evaluate(array('a', 'b')));
		$this->assertFalse($constraint->evaluate(array('a', NULL)));

		$this->assertTrue($constraint->evaluate(array(1, 2)));
		$this->assertTrue($constraint->evaluate(array(1, 'a')));

		$this->assertTrue($constraint->evaluate(array(1.2, 1.3)));
		$this->assertTrue($constraint->evaluate(array(1.2, 'a')));

		$this->assertTrue($constraint->evaluate(array(FALSE, FALSE)));
		$this->assertTrue($constraint->evaluate(array(FALSE, 'a')));

		$this->assertTrue($constraint->evaluate(array(TRUE, TRUE)));
		$this->assertTrue($constraint->evaluate(array(TRUE, 'a')));

		$this->assertFalse($constraint->evaluate(array(array(), array())));
		$this->assertFalse($constraint->evaluate(array(array(), 'a')));

		$this->assertFalse($constraint->evaluate(array((object)array(), (object)array())));
		$this->assertFalse($constraint->evaluate(array((object)array(), 'a')));

		$this->assertFalse($constraint->evaluate(array($this->resource, $this->resource)));
		$this->assertFalse($constraint->evaluate(array($this->resource, 'a')));
	}

}