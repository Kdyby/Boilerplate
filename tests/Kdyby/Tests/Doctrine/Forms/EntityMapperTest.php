<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Forms;

use Doctrine;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Doctrine\Forms\EntityMapper;
use Kdyby\Doctrine\Forms\EntityContainer;
use Nette;
use Nette\Forms\Controls;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityMapperTest extends Kdyby\Tests\OrmTestCase
{

	/** @var \Kdyby\Doctrine\Forms\EntityMapper */
	private $mapper;



	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\Fixtures\RootEntity',
			__NAMESPACE__ . '\Fixtures\RelatedEntity',
		));

		$this->mapper = new EntityMapper($this->getDoctrine());
	}



	public function testAssigningEntity()
	{
		$entity = new Fixtures\RootEntity("Chuck Norris");
		$component = new Nette\Forms\Container;

		$this->mapper->assign($entity, $component);
		$this->assertSame($entity, current($this->mapper->getEntities()));
		$this->assertSame($component, $this->mapper->getComponent($entity));
	}



	public function testAssigningCollection()
	{
		$coll = new ArrayCollection();
		$component = new Nette\Forms\Container;

		$this->mapper->assignCollection($coll, $component);
		$this->assertSame($component, $this->mapper->getComponent($coll));
	}



	public function testSettingControlAlias()
	{
		$name = new Controls\TextInput('Name');
		$name->setParent(NULL, 'name');

		$surname = new Controls\TextInput('Surname');
		$surname->setParent(NULL, 'surname');

		$this->mapper->setControlAlias($name, 'title');

		$this->assertEquals('title', $this->mapper->getControlField($name));
		$this->assertEquals('surname', $this->mapper->getControlField($surname));
	}



	/**
	 * @return array
	 */
	public function dataItemControls()
	{
		return array(
			array(new Controls\SelectBox),
			array(new Kdyby\Forms\Controls\CheckboxList),
			array(new Controls\RadioList),
		);
	}



	/**
	 * @dataProvider dataItemControls
	 *
	 * @param \Nette\Forms\IControl $itemsControl
	 */
	public function testItemsControlLoading_RelatedPairsQuery(Nette\Forms\IControl $itemsControl)
	{
		// tested control dependencies
		$entity = new Fixtures\RootEntity("Chuck Norris");
		$container = new EntityContainer($entity);
		$container['children'] = $itemsControl;
		$this->mapper->assign($entity, $container);

		// control mapper will require DAO
		$relatedDao = $this->getMockBuilder('Kdyby\Doctrine\Dao')
			->disableOriginalConstructor()
			->getMock();
		$this->getDoctrine()->setDao(__NAMESPACE__ . '\Fixtures\RelatedEntity', $relatedDao);

		// by default, there is no mapper
		$this->assertNull($this->mapper->getControlMapper($itemsControl));
		$this->mapper->setControlMapper($itemsControl, 'name', 'id');

		// mapper should be closure
		$mapper = $this->mapper->getControlMapper($itemsControl);
		$this->assertInstanceOf('Closure', $mapper);

		// map to control
		$relatedDao->expects($this->atLeastOnce())
			->method('fetchPairs')
			->with($this->isInstanceOf('Kdyby\Doctrine\Forms\ItemPairsQuery'))
			->will($this->returnValue($items = array(1 => 'Lorem')));

		$this->mapper->loadControlItems();
		$this->assertEquals($items, $itemsControl->getItems());
	}



	public function testItemsControlLoading_FromCallback()
	{
		$entity = new Fixtures\RootEntity("Roman Štolpa se psychicky zhroutil.");
		$form = new Kdyby\Doctrine\Forms\Form($this->getDoctrine(), NULL, $this->mapper);
		$form['entity'] = $container = new EntityContainer($entity);

		$callback = $this->getMock(__NAMESPACE__ . '\CallbackMock');
		$callback->expects($this->once())
			->method('__invoke')
			->with($this->equalTo($this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity')))
			->will($this->returnValue($items = array(1 => 'Strhaná tvář', 2 => 'slzy v očích')));

		$select = $container->addSelect('children', 'Child', $callback);

		$this->mapper->loadControlItems();
		$this->assertEquals($items, $select->getItems());
	}



	public function testLoading_Controls()
	{
		$entity = new Fixtures\RootEntity("Chuck Tesla");
		$container = new EntityContainer($entity);
		$container->addText('name');

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->load();

		$this->assertEquals("Chuck Tesla", $container['name']->value);
	}



	public function testLoading_RelatedEntityValues()
	{
		$entity = new Fixtures\RootEntity("Dáda");
		$entity->daddy = new Fixtures\RelatedEntity("Motherfucker");

		$container = new EntityContainer($entity);
		$container->addText('name');
		$container['daddy'] = $daddy = new EntityContainer($this->mapper->getRelated($entity, 'daddy'));
		$daddy->addText('name');

		$this->assertEmpty($container['name']->value);
		$this->assertEmpty($daddy['name']->value);

		$this->mapper->assign($entity, $container); // this is done in attached
		$this->mapper->assign($entity->daddy, $daddy); // this is done in attached
		$this->mapper->load(); // form attached to presenter

		$this->assertEquals("Dáda", $container['name']->value);
		$this->assertEquals("Motherfucker", $daddy['name']->value);
	}



	public function testLoading_CreatingRelatedEntityValues()
	{
		$entity = new Fixtures\RootEntity("Dáda");

		$form = new Kdyby\Doctrine\Forms\Form($this->getDoctrine(), NULL, $this->mapper);
		$form['entity'] = $container = new EntityContainer($entity);
		$container->addText('name');

		// when requested relation, mapper tries to create the entity
		$this->assertNull($entity->daddy);
		$daddy = $container->addOne('daddy');
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $entity->daddy);

		$daddy->addText('name');

		$this->assertEmpty($container['name']->value);
		$this->assertEmpty($daddy['name']->value);

		$this->mapper->load();

		$this->assertEquals("Dáda", $container['name']->value);
		$this->assertEmpty($daddy['name']->value);
	}

}
