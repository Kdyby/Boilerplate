<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Forms;

use Kdyby;
use Kdyby\Doctrine\Forms\Form;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormTest extends Kdyby\Tests\OrmTestCase
{

	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\Fixtures\RootEntity',
			__NAMESPACE__ . '\Fixtures\RelatedEntity',
		));
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\Form $form
	 * @return \Nette\Application\UI\Presenter|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachForm(Form $form)
	{
		$presenter = $this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));
		$form->setParent($presenter, 'form');
		return $presenter;
	}



	public function testLoadingEntityValuesToForm()
	{
		$entity = new Fixtures\RootEntity("Dáda");

		$form = new Form($this->getDoctrine(), $entity);
		$form->addText('name');

		$this->attachForm($form); // when attached to presenter, loads values from entities
		$this->assertEquals("Dáda", $form['name']->value);
	}



	public function testLoadingRelatedEntityValuesToForm()
	{
		$entity = new Fixtures\RootEntity("Dáda");
		$entity->daddy = new Fixtures\RelatedEntity("Motherfucker");

		$form = new Form($this->getDoctrine(), $entity);
		$form->addText('name');
		$daddy = $form->addOne('daddy');
		$daddy->addText('name');

		$this->assertEmpty($form['name']->value);
		$this->assertEmpty($daddy['name']->value);

		$this->attachForm($form); // when attached to presenter, loads values from entities
		$this->assertEquals("Dáda", $form['name']->value);
		$this->assertEquals("Motherfucker", $daddy['name']->value);
	}



	/**
	 * @return array
	 */
	public function dataRootWithChildren()
	{
		$entity = new Fixtures\RootEntity("Tahle");
		$entity->children[] = new Fixtures\RelatedEntity('krásná', $entity);
		$entity->children[] = new Fixtures\RelatedEntity('mladinká', $entity);
		$entity->children[] = new Fixtures\RelatedEntity('dívenka', $entity);
		return array(
			array($entity, array(1 => 'krásná', 'mladinká', 'dívenka'))
		);
	}



	/**
	 * @dataProvider dataRootWithChildren
	 *
	 * @param object $entity
	 * @param array $items
	 */
	public function testLoadingSelectItemsFromRepository($entity, array $items)
	{
		$this->getDao($entity)->save($entity);

		$form = new Form($this->getDoctrine(), $entity);
		$select = $form->addSelect('children', 'Child', 'name');

		$this->attachForm($form); // when attached to presenter, loads control items
		$this->assertEquals($items, $select->getItems());
	}



	/**
	 * @dataProvider dataRootWithChildren
	 *
	 * @param object $entity
	 * @param array $items
	 */
	public function testLoadingRadioItemsFromRepository($entity, array $items)
	{
		$this->getDao($entity)->save($entity);

		$form = new Form($this->getDoctrine(), $entity);
		$radio = $form->addRadioList('children', 'Child', 'name');

		$this->attachForm($form); // when attached to presenter, loads control items
		$this->assertEquals($items, $radio->getItems());
	}



	/**
	 * @dataProvider dataRootWithChildren
	 *
	 * @param object $entity
	 * @param array $items
	 */
	public function testLoadingCheckboxItemsFromRepository($entity, array $items)
	{
		$this->getDao($entity)->save($entity);

		$form = new Form($this->getDoctrine(), $entity);
		$check = $form->addCheckboxList('children', 'Child', 'name');

		$this->attachForm($form); // when attached to presenter, loads control items
		$this->assertEquals($items, $check->getItems());
	}



	public function testLoadingSelectItemsFromCallback()
	{
		$entity = new Fixtures\RootEntity("Roman Štolpa se psychicky zhroutil.");
		$form = new Form($this->getDoctrine(), $entity);

		$items = array(1 => 'Strhaná tvář', 2 => 'slzy v očích');

		$callback = $this->getMock(__NAMESPACE__ . '\CallbackMock');
		$callback->expects($this->once())
			->method('__invoke')
			->with($this->equalTo($this->getDao(__NAMESPACE__ . '\Fixtures\RelatedEntity')))
			->will($this->returnValue($items));

		$select = $form->addSelect('children', 'Child', $callback);

		$this->attachForm($form); // when attached to presenter, loads control items
		$this->assertEquals($items, $select->getItems());
	}



	public function testCreatingRelatedEntityValuesToForm()
	{
		$entity = new Fixtures\RootEntity("Dáda");

		$form = new Form($this->getDoctrine(), $entity);
		$form->addText('name');

		// when requested relation, mapper tries to create the entity
		$this->assertNull($entity->daddy);
		$daddy = $form->addOne('daddy');
		$this->assertInstanceOf(__NAMESPACE__ . '\Fixtures\RelatedEntity', $entity->daddy);

		$daddy->addText('name');

		$this->assertEmpty($form['name']->value);
		$this->assertEmpty($daddy['name']->value);

		$this->attachForm($form); // when attached to presenter, loads values from entities
		$this->assertEquals("Dáda", $form['name']->value);
		$this->assertEmpty($daddy['name']->value);
	}



	/**
	 * @return array
	 */
	public function dataItemControls()
	{
		return array(
			array('addSelect', 'Nette\Forms\Controls\SelectBox'),
			array('addCheckboxList', 'Kdyby\Forms\Controls\CheckboxList'),
			array('addRadioList', 'Nette\Forms\Controls\RadioList'),
		);
	}



	/**
	 * @dataProvider dataItemControls
	 *
	 * @param string $method
	 * @param string $type
	 */
	public function testSelectBoxHasMapper($method, $type)
	{
		$entity = new Fixtures\RootEntity("Hvězda Ordinace Sandra Nováková zrušila svatbu. Víme o tom vše");
		$container = new EntityContainer($entity);

		$this->attachContainer($container, $mapper = $this->mockMapper('setControlMapper'));
		$mapper->expects($this->once())
			->method('setControlMapper')
			->with($this->isInstanceOf($type), $this->equalTo('name'));

		$container->$method('children', 'Name', 'name');
	}



	/**
	 * @dataProvider dataItemControls
	 *
	 * @param string $method
	 */
	public function testSelectBoxReceivesItemsArray($method)
	{
		$entity = new Fixtures\RootEntity("Mariah Carey se bojí o manžela: Selhaly mu ledviny");
		$container = new EntityContainer($entity);

		$this->attachContainer($container, $mapper = $this->mockMapper('setControlMapper'));
		$mapper->expects($this->never())->method('setControlMapper');

		$children = $container->$method('children', 'Name', $items = array(1 => 'title'));
		$this->assertEquals($items, $children->getItems());
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CallbackMock extends Nette\Object
{

	public function __invoke()
	{

	}

}
