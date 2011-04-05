<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Nette\Application\Presenter;
use Nette\Forms\FormContainer;
use Kdyby;



/**
 * Grid column
 *
 * @author Filip Procházka
 */
class FormColumn extends BaseColumn
{

	/** @var Nette\Forms\IFormControl */
	private $controlPrototype;

	/** @var array */
	private $controls = array();



	/**
	 * @param Nette\Forms\IFormControl $control
	 */
	public function __construct(Nette\Forms\IFormControl $control)
	{
		if ($control->parent) {
			throw new \InvalidArgumentException("Control " . $control->name . " can't be attached.");
		}

		$this->controlPrototype = $control;
		$this->monitor('Nette\Application\Presenter');
	}



	/**
	 * @return Nette\Forms\IFormControl
	 */
	public function getControlPrototype()
	{
		return $this->controlPrototype;
	}



	/**
	 * @param Nette\Forms\IFormControl $control
	 * @return Nette\Forms\IFormControl
	 */
	protected function addControl(Nette\Forms\IFormControl $control)
	{
		if (!$control->parent) {
			throw new \InvalidStateException("Control named '" . $control->name . "' in column '" . $this->name . "' must have parent.");
		}

		return $this->controls[$control->name] = $control;
	}



	/**
	 * @return array
	 */
	public function getControls()
	{
		return $this->controls;
	}



	/**
	 * @param Nette\ComponentContainer $obj
	 * @return void
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Presenter) {
			return;
		}

		$form = $this->getGrid()->getForm();
		$container = $form->addContainer($this->name);
		$this->buildControls($container);
	}



	/**
	 * @return FormContainer
	 */
	protected function getContainer()
	{
		$form = $this->getGrid()->getForm();
		$container = $form->getComponent($this->name, FALSE);

		if (!$container) {
			throw new \InvalidStateException("Column is not yet attached to presenter.");
		}

		return $container;
	}



	/**
	 * @param FormContainer $container
	 * @return FormContainer
	 */
	protected function buildControls(FormContainer $container)
	{
		if ($this->controlPrototype === NULL) {
			throw new \InvalidStateException("Control prototype cannot be null.");
		}

		if ($this->controlPrototype->parent) {
			throw new \InvalidStateException("Control can't be attached.");
		}

		if ($this->controlPrototype->getRules()->getIterator()->count() > 0) {
			throw new \NotSupportedException("Bug: rules clonning. Sorry.");
		}

		$itemsCount = $this->getGrid()->getPaginator()->getItemsPerPage();

		for ($index = 0; $index < $itemsCount ;$index++) {
			$container->addComponent($control = clone $this->controlPrototype, $index);
			$this->addControl($control);
		}

		return $container;
	}



	/**
	 * @return Nette\Forms\IFormControl
	 */
	public function getControl()
	{
		$index = $this->getGrid()->getCurrentIndex();
		return $this->controls[$index];
	}



	/**
	 * @return array
	 */
	public function getValues()
	{
		$values = array();
		foreach ($this->getControls() as $control) {
			$values[$control->name] = $control->value;
		}

		return $values;
	}

}