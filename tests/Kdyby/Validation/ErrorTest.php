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
class ErrorTest extends Kdyby\Testing\Test
{


	public function testHoldsMessage()
	{
		$message = 'message';
		$error = new Kdyby\Validation\Error($message);
		$this->assertSame($message, $error->getMessage());
	}



	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testMessageIsNotStringException()
	{
		new Kdyby\Validation\Error((object)array());
	}



	public function testHoldsInvalidObject()
	{
		$invalidObject = (object)array();
		$error = new Kdyby\Validation\Error('invalid', $invalidObject);
		$this->assertSame($invalidObject, $error->getInvalidObject());
	}



	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testInvalidObjectIsNotObjectException()
	{
		new Kdyby\Validation\Error('message', 'object');
	}



	public function testHoldsPropertyName()
	{
		$propertyName = 'username';
		$error = new Kdyby\Validation\Error('invalid', (object)array(), $propertyName);
		$this->assertSame($propertyName, $error->getPropertyName());
	}



	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testPropertyNameIsNotStringException()
	{
		new Kdyby\Validation\Error('message', 'object', (object)array());
	}



	/**
	 * @expectedException Kdyby\Validation\Error
	 */
	public function testErrorCanBeThrown()
	{
		throw new Kdyby\Validation\Error('test');
	}


}