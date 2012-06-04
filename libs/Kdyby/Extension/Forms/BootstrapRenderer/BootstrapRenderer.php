<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Forms\BootstrapRenderer;

use Nette;
use Nette\Forms\Controls;
use Nette\Latte\Macros\FormMacros;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;



/**
 * Created with twitter bootstrap in mind.
 *
 * Usage:
 * $form->addRenderer(new Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer);
 *
 * @author Pavel Ptacek
 * @author Filip Procházka
 */
class BootstrapRenderer extends Nette\Object implements Nette\Forms\IFormRenderer
{

	/**
	 * set to false, if you want to display the field errors also as form errors
	 * @var bool
	 */
	public $errorsAtInputs = TRUE;

	/**
	 * Groups that should be rendered first
	 */
	public $priorGroups = array();

	/**
	 * @var \Nette\Forms\Form
	 */
	private $form;

	/**
	 * @var \Nette\Templating\Template|\stdClass
	 */
	private $template;



	/**
	 * @param \Nette\Templating\FileTemplate $template
	 */
	public function __construct(FileTemplate $template = NULL)
	{
		if ($template === NULL) {
			$template = new FileTemplate();
			$template->registerFilter(new \Nette\Latte\Engine());
		}

		$template->setFile(__DIR__ . '/@form.latte');
		$this->template = $template;
	}



	/**
	 * Render the templates
	 *
	 * @param \Nette\Forms\Form $form
	 * @param string $mode
	 *
	 * @return void
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL)
	{
		if ($this->form !== $form) {
			$this->form = $form;

			// translators
			if ($translator = $this->form->getTranslator()) {
				$this->template->setTranslator($translator);
			}

			// controls placeholders & classes
			foreach ($this->form->getControls() as $control) {
				$this->prepareControl($control);
			}

			$formEl = $form->getElementPrototype();
			if (!$formEl->class || stripos('form-', (string)$formEl->class) === FALSE) {
				$formEl->addClass('form-horizontal');
			}
		}

		$this->template->form = $this->form;
		$this->template->_form = $this->form;
		$this->template->renderer = $this;

		if ($mode === NULL) {
			$this->template->render();

		} elseif ($mode === 'begin') {
			FormMacros::renderFormBegin($this->form, array());

		} elseif ($mode === 'end') {
			FormMacros::renderFormEnd($this->form);

		} else {
			$this->template->setFile(__DIR__ . '/@parts.latte');
			$this->template->mode = $mode;
			$this->template->render();
			$this->template->setFile(__DIR__ . '/@form.latte');
		}
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 */
	private function prepareControl(Controls\BaseControl $control)
	{
		$translator = $this->form->getTranslator();
		$control->setOption('rendered', FALSE);

		if ($control->isRequired()) {
			$control->getLabelPrototype()
				->addClass('required');
		}

		$el = $control->getControlPrototype();
		if ($el->getName() === 'input') {
			$el->class(strtr($el->type, array(
				'password' => 'text',
				'file' => 'text',
				'submit' => 'button',
				'image' => 'imagebutton',
			)), TRUE);
		}

		if ($placeholder = $control->getOption('placeholder')) {
			if (!$placeholder instanceof Html && $translator) {
				$placeholder = $translator->translate($placeholder);
			}
			$el->placeholder($placeholder);
		}

		if ($control->controlPrototype->type === 'email') {
			$email = Html::el('span', array('class' => 'add-on'))
				->setText('@');

			$control->setOption('input-prepend', $email);
		}

		if ($control instanceof Nette\Forms\ISubmitterControl) {
			$el->addClass('btn');
		}
	}



	/**
	 * @return array
	 */
	public function findErrors()
	{
		if (!$formErrors = $this->form->getErrors()) {
			return array();
		}

		if (!$this->errorsAtInputs) {
			return $formErrors;
		}

		foreach ($this->form->getControls() as $control) {
			/** @var \Nette\Forms\Controls\BaseControl $control */
			if (!$control->hasErrors()) {
				continue;
			}

			$formErrors = array_diff($formErrors, $control->getErrors());
		}

		// If we have translator, translate!
		if ($translator = $this->form->getTranslator()) {
			foreach ($formErrors as $key => $val) {
				$formErrors[$key] = $translator->translate($val);
			}
		}

		return $formErrors;
	}



	/**
	 * @throws \RuntimeException
	 * @return object[]
	 */
	public function findGroups()
	{
		$formGroups = $visitedGroups = array();
		foreach ($this->priorGroups as $i => $group) {
			if (!$group instanceof Nette\Forms\ControlGroup) {
				if (!$group = $this->form->getGroup($group)) {
					$groupName = (string)$this->priorGroups[$i];
					throw new \RuntimeException("Form has no group $groupName.");
				}
			}

			$visitedGroups[] = $group;
			if ($group = $this->processGroup($group)) {
				$formGroups[] = $group;
			}
		}

		foreach ($this->form->groups as $group) {
			if (!in_array($group, $visitedGroups, TRUE) && ($group = $this->processGroup($group))) {
				$formGroups[] = $group;
			}
		}

		return $formGroups;
	}



	/**
	 * @return array
	 */
	public function findControls(Nette\Forms\Container $container = NULL)
	{
		if ($container === NULL) {
			$container = $this->form;
		}

		$controls = iterator_to_array($container->getControls());
		return array_filter($controls, function (Controls\BaseControl $control) {
			return !$control->getOption('rendered');
		});
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\ControlGroup $group
	 *
	 * @return object
	 */
	public function processGroup(Nette\Forms\ControlGroup $group)
	{
		if (!$group->getOption('visual') || !$group->getControls()) {
			return NULL;
		}

		$groupLabel = $group->getOption('label');
		$groupDescription = $group->getOption('description');

		// If we have translator, translate!
		if ($translator = $this->form->getTranslator()) {
			if (!$groupLabel instanceof Html) {
				$groupLabel = $translator->translate($groupLabel);
			}
			if (!$groupDescription instanceof Html) {
				$groupDescription = $translator->translate($groupDescription);
			}
		}

		$controls = $group->getControls();

		// fake group
		return (object)array(
			'template' => $group->getOption('template'),
			'controls' => array_filter($controls, function (Controls\BaseControl $control) {
					return !$control->getOption('rendered')
						&& !$control instanceof Controls\HiddenField;
				}),
			'label' => $groupLabel,
			'description' => $groupDescription,
		);
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\Controls\BaseControl $control
	 *
	 * @return string
	 */
	public static function getControlName(Controls\BaseControl $control)
	{
		return $control->lookupPath('Nette\Forms\Form');
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\Controls\BaseControl $control
	 *
	 * @return \Nette\Utils\Html
	 */
	public static function getControlDescription(Controls\BaseControl $control)
	{
		if (!$desc = $control->getOption('description')) {
			return Html::el();
		}

		// If we have translator, translate!
		if (!$desc instanceof Html && ($translator = $control->form->getTranslator())) {
			$desc = $translator->translate($desc); // wtf?
		}

		// create element
		return Html::el('p', array('class' => 'help-block'))
			->{$desc instanceof Html ? 'add' : 'setText'}($desc);
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\Controls\BaseControl $control
	 *
	 * @return \Nette\Utils\Html
	 */
	public function getControlError(Controls\BaseControl $control)
	{
		if (!($errors = $control->getErrors()) || !$this->errorsAtInputs) {
			return Html::el();
		}
		$error = reset($errors);

		// If we have translator, translate!
		if (!$error instanceof Html && ($translator = $control->form->getTranslator())) {
			$error = $translator->translate($error); // wtf?
		}

		// create element
		return Html::el('p', array('class' => 'help-inline'))
			->{$error instanceof Html ? 'add' : 'setText'}($error);
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\Controls\BaseControl $control
	 *
	 * @return string
	 */
	public static function getControlTemplate(Controls\BaseControl $control)
	{
		return $control->getOption('template');
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return bool
	 */
	public static function isButton(Nette\Forms\IControl $control)
	{
		return $control instanceof Controls\Button;
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return bool
	 */
	public static function isSubmitButton(Nette\Forms\IControl $control = NULL)
	{
		return $control instanceof Nette\Forms\ISubmitterControl;
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return bool
	 */
	public static function isCheckbox(Nette\Forms\IControl $control)
	{
		return $control instanceof Controls\Checkbox;
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return bool
	 */
	public static function isRadioList(Nette\Forms\IControl $control)
	{
		return $control instanceof Controls\RadioList;
	}



	/**
	 * @internal
	 *
	 * @param \Nette\Forms\Controls\RadioList $control
	 *
	 * @return bool
	 */
	public static function getRadioListItems(Controls\RadioList $control)
	{
		$items = array();
		foreach ($control->items as $key => $value) {
			$html = $control->getControl($key);
			$html[1]->addClass('radio');

			$items[$key] = (object)array(
				'input' => $html[0],
				'label' => $html[1],
				'caption' => $html[1]->getText()
			);
		}

		return $items;
	}

}
