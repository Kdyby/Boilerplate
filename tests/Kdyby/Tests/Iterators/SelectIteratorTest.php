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
use Kdyby\Iterators\SelectIterator;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SelectIteratorTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Iterators\SelectIterator */
	private $iterator;



	protected function setUp()
	{
		$this->iterator = new SelectIterator(new \ArrayIterator(range(1, 100)));
	}



	public function testFilteringWithOneFilter()
	{
		$result = $this->iterator->select(function (SelectIterator $iterator) {
			return $iterator->current() <= 10;
		})->toArray();

		$this->assertSame(range(1,10), array_values($result));
	}



	public function testFilteringWithMultipleFilters()
	{
		$result = $this->iterator
			->select(function (SelectIterator $iterator) {
				return $iterator->current() <= 10;
			})->select(function (SelectIterator $iterator) {
				return $iterator->current()%2 == 0;
			})->toArray();

		$this->assertSame(range(2,10,2), array_values($result));
	}

}
