<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Application\UI;

use Kdyby;
use Kdyby\Application\UI\Form;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FormTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Tests\Application\UI\MockForm */
	private $form;


	public function setup()
	{
		$presenter = $this->getMock('Nette\Application\UI\Presenter', array(), array($this->getContext()));

		$this->form = new MockForm();
		$this->form->setParent($presenter, 'form');
	}



	public function testCreation()
	{
		$this->assertInstanceOf('Nette\Forms\Controls\TextInput', $this->form->getComponent('name', FALSE));
	}



	public function testAttachingEvents()
	{
		$this->assertEventHasCallback(array($this->form, 'handleSuccess'), $this->form, 'onSuccess');
		$this->assertEventHasCallback(array($this->form, 'handleError'), $this->form, 'onError');
		$this->assertEventHasCallback(array($this->form, 'handleValidate'), $this->form, 'onValidate');
	}



	public function testAttachingButtonEvents()
	{
		$this->assertEventHasCallback(array($this->form, 'handleSaveClick'), $this->form['save'], 'onClick');
		$this->assertEventHasCallback(array($this->form, 'handleSaveInvalidClick'), $this->form['save'], 'onInvalidClick');
		$this->assertEventHasCallback(array($this->form, 'handleFooBarEditClick'), $this->form['foo']['bar']['edit'], 'onClick');
	}

}



class MockForm extends Form
{

	protected function configure()
	{
		$this->addText('name', 'Jméno');

		$this->addSubmit('save', 'Odeslat');

		$bar = $this->addContainer('foo')->addContainer('bar');
		$bar->addSubmit('edit', 'Odeslat');
	}



	public function handleSuccess()
	{
		throw new Kdyby\NotImplementedException;
	}



	public function handleError()
	{
		throw new Kdyby\NotImplementedException;
	}



	public function handleValidate()
	{
		throw new Kdyby\NotImplementedException;
	}



	public function handleSaveClick()
	{
		throw new Kdyby\NotImplementedException;
	}



	public function handleSaveInvalidClick()
	{
		throw new Kdyby\NotImplementedException;
	}



	public function handleFooBarEditClick()
	{
		throw new Kdyby\NotImplementedException;
	}

}
