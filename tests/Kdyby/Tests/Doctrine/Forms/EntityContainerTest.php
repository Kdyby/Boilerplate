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
use Kdyby\Doctrine\Forms\EntityContainer;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityContainerTest extends Kdyby\Tests\OrmTestCase
{

	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\Fixtures\RootEntity',
			__NAMESPACE__ . '\Fixtures\RelatedEntity',
		));
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\EntityContainer $container
	 * @param \Kdyby\Doctrine\Forms\EntityMapper $mapper
	 *
	 * @return \Kdyby\Doctrine\Forms\Form|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachContainer(EntityContainer $container, Kdyby\Doctrine\Forms\EntityMapper $mapper = NULL)
	{
		$form = $this->getMock('Kdyby\Doctrine\Forms\Form', array('getMapper'), array($this->getDoctrine()));
		$form->expects($this->any())
			->method('getMapper')
			->will($this->returnValue($mapper ? : $this->mockMapper()));

		$container->setParent($form, 'form');
		return $form;
	}



	/**
	 * @param array $methods
	 *
	 * @return \Kdyby\Doctrine\Forms\EntityMapper|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockMapper($methods = array())
	{
		return $this->getMock('Kdyby\Doctrine\Forms\EntityMapper', (array)$methods, array($this->getDoctrine()));
	}



	public function testContainerProvidesEntity()
	{
		$entity = new Fixtures\RootEntity("Podívejte se na neskutečně vyvinutou Australanku, které ženy nevěří, že má pravá prsa");
		$container = new EntityContainer($entity);

		$this->assertSame($entity, $container->getEntity());
	}



	public function testContainerAttachesEntity()
	{
		$entity = new Fixtures\RootEntity("Víme, čím Pavlína Němcová okouzluje filmové producenty");
		$container = new EntityContainer($entity);

		$mapper = $this->mockMapper('assign');
		$mapper->expects($this->once())
			->method('assign')
			->with($this->equalTo($entity), $this->equalTo($container));

		$this->attachContainer($container, $mapper);
	}



	/**
	 * @expectedException Kdyby\InvalidStateException
	 */
	public function testContainerAttaching_InvalidParentException()
	{
		$container = new Nette\Forms\Container();
		$container['name'] = new EntityContainer(new \stdClass());
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
		$entity = new Fixtures\RootEntity("Kevin Bacon (53) a jeho žena Kyra (46) se pochlubili neuvěřitelně vypracovanými těly");
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
		$entity = new Fixtures\RootEntity("To je překvápko! Kuchař Zdeněk Pohlreich se znovu tajně oženil");
		$container = new EntityContainer($entity);

		$this->attachContainer($container, $mapper = $this->mockMapper('setControlMapper'));
		$mapper->expects($this->never())->method('setControlMapper');

		$children = $container->$method('children', 'Name', $items = array(1 => 'title'));
		$this->assertEquals($items, $children->getItems());
	}


}
