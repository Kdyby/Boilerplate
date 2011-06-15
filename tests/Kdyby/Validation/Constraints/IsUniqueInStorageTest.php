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
class IsUniqueInStorageTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Constraints\IsUniqueInStorage|\PHPUnit_Framework_MockObject_MockObject */
	private $constraint;

	/** @var Kdyby\Validation\IStorage */
	private $storage;



	public function setUp()
	{
		$this->storage = $this->getMock('Kdyby\Validation\IStorage');
		$this->constraint = new Kdyby\Validation\Constraints\IsUniqueInStorage('username', $this->storage);
	}



	public function testEvaluateIsUnique()
	{
		$username = 'HosipLan';

		$this->storage->expects($this->once())
			->method('countByAttribute')
			->with($this->equalTo('username'), $this->equalTo($username))
			->will($this->returnValue(0));

		$this->assertTrue($this->constraint->evaluate($username));
	}



	public function testEvaluateIsNotUnique()
	{
		$username = 'HosipLan';

		$this->storage->expects($this->once())
			->method('countByAttribute')
			->with($this->equalTo('username'), $this->equalTo($username))
			->will($this->returnValue(1));

		$this->assertFalse($this->constraint->evaluate($username));
	}

}