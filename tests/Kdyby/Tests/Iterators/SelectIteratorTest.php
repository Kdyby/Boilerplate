<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Iterators;

use Kdyby;
use Kdyby\Iterators\SelectIterator;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
