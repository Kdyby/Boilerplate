<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Application\UI;

use Kdyby;
use Kdyby\Application\UI\Form;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormTest extends Kdyby\Tests\TestCase
{

	public function testCreation()
	{
		$form = new MockForm();
		$this->assertInstanceOf('Nette\Forms\Controls\TextInput', $form->getComponent('name', FALSE));
		$this->assertEventHasCallback(array($form, 'handleSuccess'), $form, 'onSuccess');
		$this->assertEventHasCallback(array($form, 'handleError'), $form, 'onError');
		$this->assertEventHasCallback(array($form, 'handleValidate'), $form, 'onValidate');
		$this->assertEventHasCallback(array($form, 'handleSaveClick'), $form['save'], 'onClick');
		$this->assertEventHasCallback(array($form, 'handleSaveInvalidClick'), $form['save'], 'onInvalidClick');
		$this->assertEventHasCallback(array($form, 'handleFooBarEditClick'), $form['foo']['bar']['edit'], 'onClick');
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



	public function handleInvalidSubmit()
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
