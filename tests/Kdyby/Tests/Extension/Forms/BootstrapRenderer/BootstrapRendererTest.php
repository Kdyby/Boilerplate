<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Forms\BootstrapRenderer;

use Kdyby;
use Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BootstrapRendererTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function dataCreateRichForm()
	{
		$form = new Form();
		$form->addError("General failure!");

		$grouped = $form->addContainer('grouped');
		$grouped->currentGroup = $form->addGroup('Skupina', FALSE);
		$grouped->addText('name', 'Jméno')
			->getLabelPrototype()->addClass('test');
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
	public function dataRenderingBasics()
	{
		return $this->findInputOutput('basic/input/*.latte', 'basic/output/*.html');
	}



	/**
	 * @dataProvider dataRenderingBasics
	 *
	 * @param string $latteFile
	 * @param string $expectedOutput
	 */
	public function testRenderingBasics($latteFile, $expectedOutput)
	{
		$form = $this->dataCreateRichForm();
		$this->assertTemplateOutput($latteFile, $expectedOutput, $form);
	}



	/**
	 * @return array
	 */
	public function dataRenderingComponents()
	{
		return $this->findInputOutput('components/input/*.latte', 'components/output/*.html');
	}



	/**
	 * @dataProvider dataRenderingComponents
	 *
	 * @param string $latteFile
	 * @param string $expectedOutput
	 */
	public function testRenderingComponents($latteFile, $expectedOutput)
	{
		// create form
		$form = $this->dataCreateRichForm();
		$this->assertTemplateOutput($latteFile, $expectedOutput, $form);
	}



	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function dataCreateForm()
	{
		$form = new Form;
		$form->addText('name', 'Name');
		$form->addCheckbox('check', 'Indeed');
		$form->addUpload('image', 'Image');
		$form->addRadioList('sex', 'Sex', array(1 => 'Man', 'Woman'));
		$form->addSelect('day', 'Day', array(1 => 'Monday', 'Tuesday'));
		$form->addTextArea('desc', 'Description');
		$form->addSubmit('send', 'Odeslat');

		$form['checks'] = new \Kdyby\Forms\Controls\CheckboxList('Regions', array(
			1 => 'Jihomoravský',
			2 => 'Severomoravský',
			3 => 'Slezský',
		));

		return $form;
	}



	/**
	 * @return array
	 */
	public function dataRenderingIndividual()
	{
		return $this->findInputOutput('individual/input/*.latte', 'individual/output/*.html');
	}



	/**
	 * @dataProvider dataRenderingIndividual
	 *
	 * @param string $latteFile
	 * @param string $expectedOutput
	 */
	public function testRenderingIndividual($latteFile, $expectedOutput)
	{
		// create form
		$form = $this->dataCreateForm();
		$this->assertTemplateOutput($latteFile, $expectedOutput, $form);
	}



	/**
	 * @param $latteFile
	 * @param $expectedOutput
	 * @param \Nette\Application\UI\Form $form
	 * @throws \Exception
	 */
	protected function assertTemplateOutput($latteFile, $expectedOutput, Form $form)
	{
		// create template
		$container = $this->createContainer();
		$template = $container->nette->createTemplate();
		/** @var \Nette\Templating\FileTemplate $template */
		$template->setCacheStorage($container->nette->templateCacheStorage);
		$template->setFile($latteFile);

		// create form
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
