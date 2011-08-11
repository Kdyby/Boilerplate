<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Iterators;

use Kdyby;
use Kdyby\Iterators\TypeIterator;
use Nette;



/**
 * @author Filip Procházka
 */
class TypeIteratorTest extends Kdyby\Testing\Test
{

	/** @var TypeIterator */
	private $iterator;



	public function setUp()
	{
		$this->iterator = new TypeIterator(new \ArrayIterator(array(
			'Kdyby\Testing\Iterators\Mocks\Bar_1',
			'Kdyby\Testing\Iterators\Mocks\Bar_2',
			'Kdyby\Testing\Iterators\Mocks\Foo_1',
			'Kdyby\Testing\Iterators\Mocks\Foo_2',
			'Kdyby\Testing\Iterators\Mocks\Foo_3',
			'Kdyby\Testing\Iterators\Mocks\Foo_4',
			'Kdyby\Testing\Iterators\Mocks\Foo_5',
			'Kdyby\Testing\Iterators\Mocks\Foo_6',
		)));
	}



	public function testSelectAbstractClasses()
	{
		$this->assertSame(array(
			'Kdyby\Testing\Iterators\Mocks\Foo_1',
			'Kdyby\Testing\Iterators\Mocks\Foo_5',
		), array_values($this->iterator->isAbstract()->getResult()));
	}



	public function testSelectSubclasses()
	{
		$this->assertSame(array(
			'Kdyby\Testing\Iterators\Mocks\Foo_2',
		), array_values($this->iterator->isSubclassOf('Kdyby\Testing\Iterators\Mocks\Foo_1')->getResult()));

		// there can't be subclass of two different classes
		$this->assertSame(array(), array_values($this->iterator->isSubclassOf('Kdyby\Testing\Iterators\Mocks\Foo_5')->getResult()));
	}



	public function testImplementsInterface()
	{
		$this->assertSame(array(
			'Kdyby\Testing\Iterators\Mocks\Foo_4',
			'Kdyby\Testing\Iterators\Mocks\Foo_5',
			'Kdyby\Testing\Iterators\Mocks\Foo_6'
		), array_values($this->iterator->isSubclassOf('Kdyby\Testing\Iterators\Mocks\Bar_2')->getResult()));
	}

}


namespace Kdyby\Testing\Iterators\Mocks;

	interface Bar_1 { }
	interface Bar_2 extends Bar_1 { }

	abstract class Foo_1 { }
	class Foo_2 extends Foo_1 { }

	class Foo_3 implements Bar_1 { }
	class Foo_4 implements Bar_2 { }
	abstract class Foo_5 implements Bar_2 { }
	class Foo_6 extends Foo_5 { }
