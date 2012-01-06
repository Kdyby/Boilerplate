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
use Kdyby\Doctrine\Forms\CollectionContainer;
use Kdyby\Doctrine\Forms\Form;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CollectionContainerTest extends Kdyby\Tests\OrmTestCase
{

	public function setUp()
	{
		$this->createOrmSandbox(array(
			__NAMESPACE__ . '\Fixtures\RootEntity',
			__NAMESPACE__ . '\Fixtures\RelatedEntity',
		));
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\CollectionContainer $container
	 * @param \Kdyby\Doctrine\Forms\EntityMapper $mapper
	 *
	 * @return \Kdyby\Doctrine\Forms\Form|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachContainer(CollectionContainer $container, Kdyby\Doctrine\Forms\EntityMapper $mapper = NULL)
	{
		$form = $this->getMock('Kdyby\Doctrine\Forms\Form', array('getMapper'), array($this->getDoctrine()));
		$form->expects($this->any())
			->method('getMapper')
			->will($this->returnValue($mapper ? : $this->mockMapper()));

		$container->setParent($form, 'form');
		return $form;
	}



	/**
	 * @param \Kdyby\Doctrine\Forms\Form $form
	 *
	 * @return \Nette\Application\UI\Presenter|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function attachForm(Form $form)
	{
		$presenter = $this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));
		$form->setParent($presenter, 'form');
		return $presenter;
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



	public function testContainerProvidesCollection()
	{
		$coll = new Doctrine\Common\Collections\ArrayCollection();
		$container = new CollectionContainer($coll, function () {});

		$this->assertSame($coll, $container->getCollection());
	}



	public function testContainerCreatesChildrenAndAttachesEntity()
	{
		$entity = new Fixtures\RootEntity;
		$entity->children[] = $rel = new Fixtures\RelatedEntity;

		$mapper = $this->mockMapper('assign');
		$form = new Form($this->getDoctrine(), NULL, $mapper);
		$form['coll'] = $container = new CollectionContainer($entity->children, function () { }, $mapper);

		$mapper->expects($this->once())
			->method('assign')
			->with($this->equalTo($rel), $this->isInstanceOf('Kdyby\Doctrine\Forms\EntityContainer'));

		$this->attachForm($form);
	}



	/**
	 * @expectedException Kdyby\InvalidStateException
	 */
	public function testContainerAttaching_InvalidParentException()
	{
		$container = new Nette\Forms\Container();
		$container['name'] = new CollectionContainer(new ArrayCollection(), function () { });
	}

}
