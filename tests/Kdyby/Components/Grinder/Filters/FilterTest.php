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
use Kdyby\Components\Grinder\Filters\Filter;
use Kdyby\Testing\Components\Grinder\GenericComponentMock;
use Nette;



/**
 * @author Filip Procházka
 */
class FilterTest extends Kdyby\Testing\Test
{

	/** @var Grinder\Filters\FiltersMap */
	private $filtersMap;

	/** @var Grinder\Filters\IFragmentsBuilder */
	private $fragmentBuilder;



	public function setup()
	{
		$this->fragmentBuilder = $this->getMock('Kdyby\Components\Grinder\Filters\IFragmentsBuilder');
		$this->filtersMap = $this->getMock(
				'Kdyby\Components\Grinder\Filters\FiltersMap',
				array(), // methods
				array($this->fragmentBuilder) // arguments
			);
	}



	public function testConstructionAndItsArguments()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$this->assertSame('name', $filter->getName());
		$this->assertSame('column', $filter->getColumn());
		$this->assertSame($this->filtersMap, $filter->getMap());
	}



	public function testCarryingMethod()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setMethod(array($this, 'startup'));
		$this->assertEquals(callback($this, 'startup'), $filter->getMethod());
	}



	public function testCarryingSource()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSource(array($this, 'startup'));
		$this->assertEquals(callback($this, 'startup'), $filter->getSource());
	}



	public function testCarryingControl()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$control = new GenericComponentMock();

		$filter->setControl($control);
		$this->assertSame($control, $filter->getControl());
	}



	public function testCarryingSkipEmpty()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSkipEmpty(TRUE);
		$this->assertTrue($filter->getSkipEmpty());
	}



	public function testCarryingDefaultValue()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setDefaultValue('value');
		$this->assertSame('value', $filter->getDefaultValue());
	}



	public function testCarryingType()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setType('string');
		$this->assertSame('string', $filter->getType());
	}



	public function testCarryingSqlType()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSqlType('s');
		$this->assertSame('s', $filter->getSqlType());
	}



	public function testGettingValueWithoutCastingType()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');

		$source = $this->getMock('Kdyby\Testing\Components\Grinder\Filters\SourceMock');
		$source->expects($this->exactly(1))
			->method('source')
			->will($this->returnValue(3.145));

		$filter->setSource(array($source, 'source'));

		$this->assertSame(3.145, $filter->getValue());
		$this->assertSame(3.145, $filter->getValue());
	}



	public function testGettingDefaultValueWithoutCastingType()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setDefaultValue(10);

		$source = $this->getMock('Kdyby\Testing\Components\Grinder\Filters\SourceMock');
		$source->expects($this->once())
			->method('source')
			->will($this->returnValue(NULL));

		$filter->setSource(array($source, 'source'));

		$this->assertSame(10, $filter->getValue());
		$this->assertSame(10, $filter->getValue());
	}



	public function testGettingScalarValueWithCastingType()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSource(function () { return '10'; });
		$filter->setType('integer');

		$this->assertSame(10, $filter->getValue());
	}



	public function testGettingNonscalarValueWithCastingType()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSource(function () { return array('10', '20', '30'); });
		$filter->setType('integer');

		$value = $filter->getValue();
		$this->assertInternalType('array', $value);
		$this->assertContainsOnly('integer', $value);
		$this->assertSame(array(10, 20, 30), $value);
	}



	public function testCreationOfFragments()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSource(function () { return 10; });

		$method = $this->getMock('Kdyby\Testing\Components\Grinder\Filters\MethodMock');
		$method->expects($this->once())
			->method('method')
			->with($this->equalTo(10), $this->equalTo($filter))
			->will($this->returnValue(array()));
		$filter->setMethod(array($method, 'method'));

		$this->assertInternalType('array', $filter->createFragments());
	}



	/**
	 * @expectedException Nette\InvalidStateException
	 */
	public function testCreationOfFragmentsWithoutSourceException()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setMethod(function () {  });
		$filter->createFragments();
	}



	/**
	 * @expectedException Nette\InvalidStateException
	 */
	public function testCreationOfFragmentsWithoutMethodException()
	{
		$filter = new Filter($this->filtersMap, 'name', 'column');
		$filter->setSource(function () {  });
		$filter->createFragments();
	}

}