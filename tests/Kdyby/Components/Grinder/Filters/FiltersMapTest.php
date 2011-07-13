<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Components\Grinder\Filters;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;



/**
 * @author Filip Procházka
 */
class FiltersMapTest extends Kdyby\Testing\Test
{

	/** @var Grinder\Filters\FiltersMap */
	private $filtersMap;

	/** @var Grinder\Filters\IFragmentsBuilder */
	private $fragmentBuilder;



	public function setUp()
	{
		$this->fragmentBuilder = $this->getMock('Kdyby\Components\Grinder\Filters\IFragmentsBuilder');
		$this->filtersMap = new Grinder\Filters\FiltersMap($this->fragmentBuilder);
	}



	public function testIsInstanceofIteratorAggregate()
	{
		$this->assertinstanceOf('IteratorAggregate', $this->filtersMap);
	}



	public function testReturnsFragmentBuilder()
	{
		$this->assertSame($this->fragmentBuilder, $this->filtersMap->getFragmentsBuilder());
	}



	public function testCreationOfFilterReturnsInstanceOfFilter()
	{
		$filter = $this->filtersMap->create('name', 'column');
		$this->assertInstanceOf('Kdyby\Components\Grinder\Filters\Filter', $filter);
	}



	public function testCreationOfFilterNameHasHigherPriority()
	{
		$filter = $this->filtersMap->create(NULL, 'column_name');
		$this->assertSame('columnname', $filter->getName());
		$this->assertSame('column_name', $filter->getColumn());

		$filter = $this->filtersMap->create('name', 'column_name');
		$this->assertSame('name', $filter->getName());
		$this->assertSame('column_name', $filter->getColumn());
	}



	public function testCreationOfFilterPassingFiltersMap()
	{
		$filter = $this->filtersMap->create('name', 'column');
		$this->assertSame($this->filtersMap, $filter->getMap());
	}



	public function testCreationOfFilterPassingDataSource()
	{
		$dataSource = callback($this, 'fake');
		$filter = $this->filtersMap->create('name', 'column', $dataSource);
		$this->assertSame($dataSource, $filter->getSource());
	}



	public function testCreationOfFilterPassingMethodName()
	{
		$filter = $this->filtersMap->create('name', 'column', NULL, 'equals');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildEquals'), $filter->getMethod());
	}



	public function testCreationOfFilterTranslatingMethodName()
	{
		$filter = $this->filtersMap->create('nameEquals', 'column', NULL, '=');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildEquals'), $filter->getMethod());

		$filter = $this->filtersMap->create('nameLower', 'column', NULL, '<');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildLower'), $filter->getMethod());

		$filter = $this->filtersMap->create('nameLowerOrEquals', 'column', NULL, '<=');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildLowerOrEquals'), $filter->getMethod());

		$filter = $this->filtersMap->create('nameHigher', 'column', NULL, '>');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildHigher'), $filter->getMethod());

		$filter = $this->filtersMap->create('nameHigherOrEquals', 'column', NULL, '>=');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildHigherOrEquals'), $filter->getMethod());

		$filter = $this->filtersMap->create('nameLike', 'column', NULL, '~');
		$this->assertEquals(callback($this->fragmentBuilder, 'buildLike'), $filter->getMethod());
	}



	public function testAddingOfFilterValid()
	{
		$filter = new Grinder\Filters\Filter($this->filtersMap, 'abcdefghijklmnopqrstuvwxyz1234567890_-', 'column');
		$this->filtersMap->add($filter);
	}



	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testAddingOfFilterWithNonStringNameException()
	{
		$filter = new Grinder\Filters\Filter($this->filtersMap, (object)NULL, 'column');
		$this->filtersMap->add($filter);
	}



	/**
	 * @expectedException Nette\InvalidArgumentException
	 */
	public function testAddingOfFilterWithInvalidNameException()
	{
		$filter = new Grinder\Filters\Filter($this->filtersMap, '+ø→↓←ŧ¶€|ł', 'column');
		$this->filtersMap->add($filter);
	}



	/**
	 * @expectedException Nette\OutOfRangeException
	 */
	public function testAddingOfFilterNonUniqueNameException()
	{
		$filterOne = new Grinder\Filters\Filter($this->filtersMap, 'name', 'column');
		$this->filtersMap->add($filterOne);

		$filterTwo = new Grinder\Filters\Filter($this->filtersMap, 'name', 'column');
		$this->filtersMap->add($filterTwo);
	}



	public function testRetrievingFilter()
	{
		$filter = $this->filtersMap->create('name', 'column');

		$this->assertTrue($this->filtersMap->has('name'));
		$this->assertSame($filter, $this->filtersMap->get('name'));
	}



	/**
	 * @expectedException Nette\OutOfRangeException
	 */
	public function testRetrievingNonExistingFilterException()
	{
		$this->filtersMap->get('name');
	}



	public function testIteratorContainsGivenFilters()
	{
		$filterOne = $this->filtersMap->create('one', 'column_name');
		$filterTwo = $this->filtersMap->create('two', 'column_name');
		$filterThree = $this->filtersMap->create('three', 'column_name');

		$iterator = $this->filtersMap->getIterator();
		$this->assertContains($filterOne, $iterator);
		$this->assertContains($filterTwo, $iterator);
		$this->assertContains($filterThree, $iterator);
	}


}