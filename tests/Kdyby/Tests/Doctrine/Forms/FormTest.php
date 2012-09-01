<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Forms;

use Kdyby;
use Kdyby\Doctrine\Forms\Form;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
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



	/**
	 * @param array $methods
	 *
	 * @return \Kdyby\Doctrine\Forms\EntityMapper|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockMapper($methods = array())
	{
		return $this->getMock('Kdyby\Doctrine\Forms\EntityMapper', (array)$methods, array($this->getDoctrine()));
	}



	public function testAttached_Load()
	{
		$mapper = $this->mockMapper(array('loadControlItems', 'load'));
		$form = new Form($this->getDoctrine(), NULL, $mapper);

		$mapper->expects($this->once())
			->method('loadControlItems')
			->withAnyParameters();

		$mapper->expects($this->once())
			->method('load')
			->withAnyParameters();

		$this->attachForm($form);
	}



	public function testAttached_Save()
	{
		$mapper = $this->mockMapper(array('loadControlItems', 'save'));
		$form = new Form($this->getDoctrine(), NULL, $mapper);
		$send = $form->addSubmit('send');
		$form->setSubmittedBy($send);

		$mapper->expects($this->once())
			->method('loadControlItems')
			->withAnyParameters();

		$mapper->expects($this->once())
			->method('save')
			->withAnyParameters();

		$this->attachForm($form);
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
		$form = new Form($this->getDoctrine(), $entity, $mapper = $this->mockMapper('setControlMapper'));

		$mapper->expects($this->once())
			->method('setControlMapper')
			->with($this->isInstanceOf($type), $this->equalTo('name'));

		$form->$method('children', 'Name')
			->setMapper('name');
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CallbackMock extends Nette\Object
{

	public function __invoke()
	{

	}

}
