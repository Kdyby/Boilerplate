<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Forms\BootstrapRenderer;

use Nette;
use Nette\Forms\Controls;
use Nette\Iterators\Filter;
use Nette\Latte\Macros\FormMacros;
use Nette\Templating\FileTemplate;
use Nette\Utils\Html;



/**
 * Created with twitter bootstrap in mind.
 *
 * <code>
 * $form->setRenderer(new Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer);
 * </code>
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
			$template->registerFilter(new Nette\Latte\Engine());

		} else {
			$template->setParameters(array_fill_keys(array(
				'control', '_control', 'presenter', '_presenter'
			), NULL));
		}

		$this->template = $template;
	}



	/**
	 * Render the templates
	 *
	 * @param \Nette\Forms\Form $form
	 * @param string $mode
	 * @param array $args
	 * @return void
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL, $args = NULL)
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
			if (!($classes = self::getClasses($formEl)) || stripos($classes, 'form-') === FALSE) {
				$formEl->addClass('form-horizontal');
			}

		} elseif ($mode === 'begin') {
			foreach ($this->form->getControls() as $control) {
				/** @var \Nette\Forms\Controls\BaseControl $control */
				$control->setOption('rendered', FALSE);
			}
		}

		$this->template->setFile(__DIR__ . '/@form.latte');
		$this->template->form = $this->form;
		$this->template->_form = $this->form;
		$this->template->renderer = $this;

		if ($mode === NULL) {
			if ($args) {
				$this->form->getElementPrototype()->addAttributes($args);
			}
			$this->template->render();

		} elseif ($mode === 'begin') {
			FormMacros::renderFormBegin($this->form, (array)$args);

		} elseif ($mode === 'end') {
			FormMacros::renderFormEnd($this->form);

		} else {
			$this->template->setFile(__DIR__ . '/@parts.latte');
			$this->template->mode = $mode;
			$this->template->render();
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
			$control->getLabelPrototype()->addClass('required');
			$control->setOption('required', TRUE);
		}

		$el = $control->getControlPrototype();
		if ($el->getName() === 'input') {
			$el->class(strtr($el->type, array(
				'password' => 'text',
				'file' => 'text',
				'submit' => 'button',
				'button' => 'button btn',
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

		} else {
			$label = $control->labelPrototype;
			if ($control instanceof Controls\Checkbox) {
				$label->addClass('checkbox');

			} else {
				$label->addClass('control-label');
			}

			$control->setOption('pairContainer', $pair = Html::el('div'));
			$pair->id = $control->htmlId . '-pair';
			$pair->addClass('control-group');
			if ($control->getOption('required', FALSE)) {
				$pair->addClass('required');
			}
			if ($control->errors) {
				$pair->addClass('error');
			}
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
	 * @param \Nette\Forms\Container $container
	 * @param boolean $buttons
	 * @return \Iterator
	 */
	public function findControls(Nette\Forms\Container $container = NULL, $buttons = NULL)
	{
		$container = $container ? : $this->form;
		return new Filter($container->getControls(), function ($control) use ($buttons) {
			$control = $control instanceof Filter ? $control->current() : $control;
			$isButton = $control instanceof Controls\Button || $control instanceof Nette\Forms\ISubmitterControl;
			return !$control->getOption('rendered')
				&& !$control instanceof Controls\HiddenField
				&& (($buttons === TRUE && $isButton) || ($buttons === FALSE && !$isButton) || $buttons === NULL);
		});
	}



	/**
	 * @internal
	 * @param \Nette\Forms\ControlGroup $group
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

		$controls = array_filter($group->getControls(), function (Controls\BaseControl $control) {
			return !$control->getOption('rendered')
				&& !$control instanceof Controls\HiddenField;
		});

		if (!$controls) {
			return NULL; // do not render empty groups
		}

		$groupAttrs = $group->getOption('container', Html::el())->setName(NULL);
		/** @var Html $groupAttrs */
		$groupAttrs->attrs += array_diff_key($group->getOptions(), array_fill_keys(array(
			'container', 'label', 'description', 'visual' // these are not attributes
		), NULL));

		// fake group
		return (object)(array(
			'controls' => $controls,
			'label' => $groupLabel,
			'description' => $groupDescription,
			'attrs' => $groupAttrs,
		) + $group->getOptions());
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return string
	 */
	public static function getControlName(Controls\BaseControl $control)
	{
		return $control->lookupPath('Nette\Forms\Form');
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
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
	 * @param \Nette\Forms\Controls\BaseControl $control
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
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return string
	 */
	public static function getControlTemplate(Controls\BaseControl $control)
	{
		return $control->getOption('template');
	}



	/**
	 * @internal
	 * @param \Nette\Forms\IControl $control
	 * @return bool
	 */
	public static function isButton(Nette\Forms\IControl $control)
	{
		return $control instanceof Controls\Button;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\IControl $control
	 * @return bool
	 */
	public static function isSubmitButton(Nette\Forms\IControl $control = NULL)
	{
		return $control instanceof Nette\Forms\ISubmitterControl;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\IControl $control
	 * @return bool
	 */
	public static function isCheckbox(Nette\Forms\IControl $control)
	{
		return $control instanceof Controls\Checkbox;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\IControl $control
	 * @return bool
	 */
	public static function isRadioList(Nette\Forms\IControl $control)
	{
		return $control instanceof Controls\RadioList;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\IControl $control
	 * @return bool
	 */
	public static function isCheckboxList(Nette\Forms\IControl $control)
	{
		foreach (array('Nette\Forms\Controls\\', 'Kdyby\Forms\Controls\\', '',) as $ns) {
			if (class_exists($class = $ns . 'CheckboxList', FALSE) && $control instanceof $class) {
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * 
	 * @internal
	 * @param \Nette\Forms\Controls\RadioList $control
	 * @return bool
	 */
	public static function getRadioListItems(Controls\RadioList $control)
	{
		$items = array();
		foreach ($control->items as $key => $value) {
			$el = $control->getControl($key);
			$el[1]->addClass('radio');

			$items[$key] = $radio = (object)array(
				'input' => $el[0],
				'label' => $el[1],
				'caption' => $el[1]->getText(),
			);

			$radio->html = clone $radio->label;
			$radio->html->insert(0, $radio->input);
		}

		return $items;
	}



	/**
	 * @internal
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @throws \Nette\InvalidArgumentException
	 * @return bool
	 */
	public static function getCheckboxListItems(Controls\BaseControl $control)
	{
		$items = array();
		foreach ($control->items as $key => $value) {
			$el = $control->getControl($key);
			$el[1]->addClass('checkbox')->addClass('inline');

			$items[$key] = $check = (object)array(
				'input' => $el[0],
				'label' => $el[1],
				'caption' => $el[1]->getText(),
			);

			$check->html = clone $check->label;
			$check->html->insert(0, $check->input);
		}

		return $items;
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @return \Nette\Utils\Html
	 */
	public static function getLabelBody(Controls\BaseControl $control)
	{
		$label = $control->getLabel();
		$label->setName(NULL);
		return $label;
	}



	/**
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @param string $class
	 * @return bool
	 */
	public static function controlHasClass(Controls\BaseControl $control, $class)
	{
		$classes = explode(' ', self::getClasses($control->controlPrototype));
		return in_array($class, $classes, TRUE);
	}



	/**
	 * @param \Nette\Utils\Html $el
	 * @return bool
	 */
	private static function getClasses(Html $el)
	{
		if (is_array($el->class)) {
			$classes = array_filter(array_merge(array_keys($el->class), $el->class), 'is_string');
			return implode(' ', $classes);
		}
		return $el->class;
	}

}
