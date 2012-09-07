<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Iterators;

use Kdyby;
use Kdyby\Iterators\TypeIterator;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TypeIteratorTest extends Kdyby\Tests\TestCase
{

	/** @var TypeIterator */
	private $iterator;



	public function setUp()
	{
		$this->iterator = new TypeIterator(new \ArrayIterator(array(
			'Kdyby\Tests\Iterators\Mocks\Bar_1',
			'Kdyby\Tests\Iterators\Mocks\Bar_2',
			'Kdyby\Tests\Iterators\Mocks\Foo_1',
			'Kdyby\Tests\Iterators\Mocks\Foo_2',
			'Kdyby\Tests\Iterators\Mocks\Foo_3',
			'Kdyby\Tests\Iterators\Mocks\Foo_4',
			'Kdyby\Tests\Iterators\Mocks\Foo_5',
			'Kdyby\Tests\Iterators\Mocks\Foo_6',
		)));
	}



	public function testSelectAbstractClasses()
	{
		$this->assertSame(array(
			'Kdyby\Tests\Iterators\Mocks\Foo_1',
			'Kdyby\Tests\Iterators\Mocks\Foo_5',
		), array_values($this->iterator->isAbstract()->getResult()));
	}



	public function testSelectSubclasses()
	{
		$it = $this->iterator->isSubclassOf('Kdyby\Tests\Iterators\Mocks\Foo_1');

		$this->assertSame(array(
			'Kdyby\Tests\Iterators\Mocks\Foo_2',
		), array_values($it->getResult()));

		// there can't be subclass of two different classes
		$this->assertEquals(array(), array_values($it->isSubclassOf('Kdyby\Tests\Iterators\Mocks\Foo_5')->getResult()));
	}



	public function testImplementsInterface()
	{
		$this->assertSame(array(
			'Kdyby\Tests\Iterators\Mocks\Foo_4',
			'Kdyby\Tests\Iterators\Mocks\Foo_5',
			'Kdyby\Tests\Iterators\Mocks\Foo_6'
		), array_values($this->iterator->isSubclassOf('Kdyby\Tests\Iterators\Mocks\Bar_2')->getResult()));
	}



	public function testIsInNamespace()
	{
		$iterator = new TypeIterator(new \ArrayIterator(array(
			'Kdyby\Tests\Iterators\Mocks\Bar_1',
			'Kdyby\Tests\Iterators\Mocks\Bar_2',
			'Kdyby\Tests\Iterators\Mocks\Foo_1',
			'Kdyby\Tests\Iterators\Mocks\Foo_2',
			'Kdyby\Tests\Iterators\Mocks\Foo_3',
			'Kdyby\Tests\Iterators\Mocks\Foo_4',
			'Kdyby\Tests\Iterators\Mocks\Foo_5',
			'Kdyby\Tests\Iterators\Mocks\Foo_6',
			'Kdyby\Tests\Iterators\Mocks\Foo\Bar',
			'Kdyby\Tests\Iterators\Mocks\Foo\Foo\Bar',
		)));

	}

}


namespace Kdyby\Tests\Iterators\Mocks;

	interface Bar_1 { }
	interface Bar_2 extends Bar_1 { }

	abstract class Foo_1 { }
	class Foo_2 extends Foo_1 { }

	class Foo_3 implements Bar_1 { }
	class Foo_4 implements Bar_2 { }
	abstract class Foo_5 implements Bar_2 { }
	class Foo_6 extends Foo_5 { }


namespace Kdyby\Tests\Iterators\Mocks\Foo;
	class Bar { }


namespace Kdyby\Tests\Iterators\Mocks\Foo\Foo;
	class Bar {}
