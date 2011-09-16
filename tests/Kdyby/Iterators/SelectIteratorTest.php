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
use Kdyby\Iterators\SelectIterator;
use Nette;



/**
 * @author Filip Procházka
 */
class SelectIteratorTest extends Kdyby\Testing\TestCase
{

	/** @var SelectIterator */
	private $iterator;



	protected function setUp()
	{
		$this->iterator = new SelectIterator(new \ArrayIterator(range(1, 100)));
	}



	public function testFilteringWithOneFilter()
	{
		$this->iterator->select(function (SelectIterator $iterator) {
			return $iterator->current() <= 10;
		});

		$this->assertSame(range(1,10), array_values($this->iterator->toArray()));
	}



	public function testFilteringWithMultipleFilters()
	{
		$this->iterator->select(function (SelectIterator $iterator) {
			return $iterator->current() <= 10;
		});

		$this->iterator->select(function (SelectIterator $iterator) {
			return $iterator->current()%2 == 0;
		});

		$this->assertSame(range(2,10,2), array_values($this->iterator->toArray()));
	}

}