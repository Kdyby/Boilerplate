<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class ResultTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Result */
	private $result;



	public function setUp()
	{
		$this->result = new Kdyby\Validation\Result();
	}



	public function testIsValidWhenInitialized()
	{
		$this->assertTrue($this->result->isValid());
		$this->assertEmpty($this->result->getErrors());
		$this->assertFalse($this->result->isFrozen());
	}



	public function testAddErrorAcceptsMessage()
	{
		$this->result->addError('test1');
		$this->result->addError('test2');

		$errors = $this->result->getErrors();

		$this->assertEquals(2, count($errors));
		$this->assertEquals(2, count($this->result->getIterator()));

		$first = reset($errors);
		$second = next($errors);

		$this->assertInstanceOf('Kdyby\Validation\Error', $first);
		$this->assertSame('test1', $first->getMessage());

		$this->assertInstanceOf('Kdyby\Validation\Error', $second);
		$this->assertSame('test2', $second->getMessage());
	}



	public function testAddErrorAcceptsErrorInstance()
	{
		$error1 = new Kdyby\Validation\Error('test1');
		$error2 = new Kdyby\Validation\Error('test2');

		$this->result->addError($error1);
		$this->result->addError($error2);

		$errors = $this->result->getErrors();

		$this->assertEquals(2, count($errors));
		$this->assertEquals(2, count($this->result->getIterator()));

		$first = reset($errors);
		$second = next($errors);

		$this->assertInstanceOf('Kdyby\Validation\Error', $first);
		$this->assertSame($error1, $first);
		$this->assertSame('test1', $first->getMessage());

		$this->assertInstanceOf('Kdyby\Validation\Error', $second);
		$this->assertSame($error2, $second);
		$this->assertSame('test2', $second->getMessage());
	}



	public function testImporting()
	{
		$result = new Kdyby\Validation\Result();
		$result->addError('test1');
		$result->addError('test2');

		$this->result->import($result);

		$errors = $this->result->getErrors();

		$this->assertEquals(2, count($errors));
		$this->assertEquals(2, count($this->result->getIterator()));

		$first = reset($errors);
		$second = next($errors);

		$this->assertInstanceOf('Kdyby\Validation\Error', $first);
		$this->assertSame('test1', $first->getMessage());

		$this->assertInstanceOf('Kdyby\Validation\Error', $second);
		$this->assertSame('test2', $second->getMessage());
	}



	/**
	 * @expectedException Nette\InvalidStateException
	 */
	public function testCantAddErrorsWhenFrozen()
	{
		$this->result->freeze();
		$this->result->addError('test');
	}



	/**
	 * @expectedException Nette\InvalidStateException
	 */
	public function testCantImportWhenFrozen()
	{
		$this->result->freeze();
		$this->result->import(new Kdyby\Validation\Result());
	}



	/**
	 * @expectedException Kdyby\Validation\Result
	 */
	public function testResultCanBeThrown()
	{
		throw $this->result;
	}

}