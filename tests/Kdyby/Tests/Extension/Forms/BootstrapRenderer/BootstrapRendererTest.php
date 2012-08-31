<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Forms\BootstrapRenderer;

use Kdyby;
use Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class BootstrapRendererTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return \Kdyby\Application\UI\Form
	 */
	protected function dataFormComponent()
	{
		$form = new Kdyby\Application\UI\Form();
		$form->addError("General failure!");

		$grouped = $form->addContainer('grouped');
		$grouped->currentGroup = $form->addGroup('Skupina', FALSE);
		$grouped->addText('name', 'Jméno');
		$grouped->addText('email', 'Email')
			->setType('email');
		$grouped->addSelect('sex', 'Pohlaví', array(1 => 'Muž', 2 => 'Žena'));
		$grouped->addCheckbox('mailing', 'Zasílat novinky');
		$grouped->addButton('add', 'Přidat');

		$grouped->addSubmit('poke', 'Šťouchnout');
		$grouped->addSubmit('poke2', 'Ještě Šťouchnout')
			->setAttribute('class', 'btn-success');

		$other = $form->addContainer('other');
		$other->currentGroup = $form->addGroup('Other', FALSE);
		$other->addRadioList('sexy', 'Sexy', array(1 => 'Ano', 2 => 'Ne'));
		$other->addPassword('heslo', 'Heslo')
			->addError('chybka!');
		$other->addSubmit('pass', "Nastavit heslo")
			->setAttribute('class', 'btn-warning');

		$form->addUpload('photo', 'Fotka');
		$form->addSubmit('up', 'Nahrát fotku');

		$form->addTextArea('desc', 'Popis');

		$form->addSubmit('submit', 'Uložit')
			->setAttribute('class', 'btn-primary');
		$form->addSubmit('delete', 'Smazat');

		return $form;
	}



	/**
	 * @return array
	 */
	public function dataRendering()
	{
		return $this->findInputOutput('input/*.latte', 'output/*.html');
	}



	/**
	 * @dataProvider dataRendering
	 *
	 * @throws \Kdyby\NotImplementedException
	 */
	public function testRendering($latteFile, $expectedOutput)
	{
		// create template
		$container = $this->createContainer();
		$template = $container->nette->createTemplate();
		/** @var \Nette\Templating\FileTemplate $template */
		$template->setCacheStorage($container->nette->templateCacheStorage);
		$template->setFile($latteFile);

		// create form
		$form = $this->dataFormComponent();
		$form->setRenderer(new BootstrapRenderer(clone $template));
		$template->setParameters(array('form' => $form, '_form' => $form));

		// render template
		ob_start();
		try {
			$template->render();
		} catch (\Exception $e) {
			ob_end_clean();
			throw $e;
		}
		$output = Strings::normalize(ob_get_clean());
		$expected = Strings::normalize(file_get_contents($expectedOutput));

		// assert
		$this->assertSame($expected, $output);
	}

}
