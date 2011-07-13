<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Components\Grinder\Filters\Fragments;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use Kdyby;
use Kdyby\Components\Grinder\Filters;
use Nette;



/**
 * @author Filip Procházka
 */
class DoctrineQueryBuilderTest extends Kdyby\Testing\Test
{

	/** @var Filters\Fragments\DoctrineQueryBuilder */
	private $fragmentsBuilder;

	/** @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject */
	private $qb;

	/** @var Filters\FiltersMap|\PHPUnit_Framework_MockObject_MockObject */
	private $filtersMap;

	/** @var Doctrine\ORM\Query\Expr */
	private $expr;



	public function setUp()
	{
		$this->qb = $this->getMock('Doctrine\ORM\QueryBuilder', array(), array(), '', FALSE);
		$this->filtersMap = $this->getMock('Kdyby\Components\Grinder\Filters\FiltersMap', array(), array(), '', FALSE);

		$this->fragmentsBuilder = new Filters\Fragments\DoctrineQueryBuilder();
		$this->fragmentsBuilder->setQueryBuilder($this->qb);

		$this->expr = new Expr();
	}



	public function testBuildingEqual()
	{
		$value = 10;

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo($value));

		$fragment = $this->fragmentsBuilder->buildEquals($value, $filter);
		$this->assertEquals($this->expr->eq('column', ':filterYoName'), $fragment);
	}



	public function testBuildingEqualWithArrayValue()
	{
		$value = array(10, 20, 30);

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo($value));

		$fragment = $this->fragmentsBuilder->buildEquals($value, $filter);
		$this->assertEquals($this->expr->in('column', ':filterYoName'), $fragment);
	}


	public function testBuildingEqualWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildEquals(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}


	public function testBuildingEqualWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildEquals(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}



	public function testBuildingLike()
	{
		$value = 10;

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo('%' . $value . '%'));

		$fragment = $this->fragmentsBuilder->buildLike($value, $filter);
		$this->assertEquals($this->expr->like('column', ':filterYoName'), $fragment);
	}



	public function testBuildingLikeWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildLike(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}



	public function testBuildingLikeWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildLike(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}



	public function testBuildingHigherOrEqualThan()
	{
		$value = 10;

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo($value));

		$fragment = $this->fragmentsBuilder->buildHigherOrEqualThan($value, $filter);
		$this->assertEquals($this->expr->gte('column', ':filterYoName'), $fragment);
	}



	public function testBuildingHigherOrEqualThanWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildHigherOrEqualThan(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}



	public function testBuildingHigherOrEqualThanWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildHigherOrEqualThan(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}



	public function testBuildingHigherThan()
	{
		$value = 10;

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo($value));

		$fragment = $this->fragmentsBuilder->buildHigherThan($value, $filter);
		$this->assertEquals($this->expr->gt('column', ':filterYoName'), $fragment);
	}



	public function testBuildingHigherThanWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildHigherThan(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}



	public function testBuildingHigherThanWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildHigherThan(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}



	public function testBuildingLowerOrEqualThan()
	{
		$value = 10;

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo($value));

		$fragment = $this->fragmentsBuilder->buildLowerOrEqualThan($value, $filter);
		$this->assertEquals($this->expr->lte('column', ':filterYoName'), $fragment);
	}



	public function testBuildingLowerOrEqualThanWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildLowerOrEqualThan(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}



	public function testBuildingLowerOrEqualThanWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildLowerOrEqualThan(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}



	public function testBuildingLowerThan()
	{
		$value = 10;

		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->once())
			->method('setParameter')
			->with($this->equalTo('filterYoName'), $this->equalTo($value));

		$fragment = $this->fragmentsBuilder->buildLowerThan($value, $filter);
		$this->assertEquals($this->expr->lt('column', ':filterYoName'), $fragment);
	}



	public function testBuildingLowerThanWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildLowerThan(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}



	public function testBuildingLowerThanWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$this->qb->expects($this->never())
			->method('setParameter');

		$fragment = $this->fragmentsBuilder->buildLowerThan(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}



	public function testBuildingNull()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$fragment = $this->fragmentsBuilder->buildNull(TRUE, $filter);
		$this->assertEquals('column IS NULL', $fragment);

		$fragment = $this->fragmentsBuilder->buildNull(FALSE, $filter);
		$this->assertEquals('column IS NOT NULL', $fragment);
	}



	public function testBuildingNullWithNullValue()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');

		$fragment = $this->fragmentsBuilder->buildNull(NULL, $filter);
		$this->assertEquals(NULL, $fragment);
	}



	public function testBuildingNullWithNullValueWithoutSkippingEmpty()
	{
		$filter = new Filters\Filter($this->filtersMap, 'yoName', 'column');
		$filter->setSkipEmpty(FALSE);

		$fragment = $this->fragmentsBuilder->buildNull(NULL, $filter);
		$this->assertEquals('column IS NULL', $fragment);
	}

}