<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Forms\Container;
use Kdyby;



/**
 * Grid column
 *
 * @author Filip Procházka
 */
class FormColumn extends BaseColumn
{

	/** @var Nette\Forms\IControl */
	private $controlPrototype;

	/** @var array */
	private $controls = array();



	/**
	 * @param Nette\Forms\IControl $control
	 */
	public function __construct(Nette\Forms\IControl $control)
	{
		if ($control->parent) {
			throw new \InvalidArgumentException("Control " . $control->name . " can't be attached.");
		}

		parent::__construct();

		$this->controlPrototype = $control;
	}



	/**
	 * @return Nette\Forms\IControl
	 */
	public function getControlPrototype()
	{
		return $this->controlPrototype;
	}



	/**
	 * @param Nette\Forms\IControl $control
	 * @return Nette\Forms\IControl
	 */
	protected function addControl(Nette\Forms\IControl $control)
	{
		if (!$control->parent) {
			throw new Nette\InvalidStateException("Control named '" . $control->name . "' in column '" . $this->name . "' must have parent.");
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
	 * @param Nette\ComponentModel\Container $obj
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
	 * @return Container
	 */
	protected function getContainer()
	{
		$form = $this->getGrid()->getForm();
		$container = $form->getComponent($this->name, FALSE);

		if (!$container) {
			throw new Nette\InvalidStateException("Column is not yet attached to presenter.");
		}

		return $container;
	}



	/**
	 * @param Container $container
	 * @return Container
	 */
	protected function buildControls(Container $container)
	{
		if ($this->controlPrototype === NULL) {
			throw new Nette\InvalidStateException("Control prototype cannot be null.");
		}

		if ($this->controlPrototype->parent) {
			throw new Nette\InvalidStateException("Control can't be attached.");
		}

		if ($this->controlPrototype->getRules()->getIterator()->count() > 0) {
			throw new Nette\NotSupportedException("Bug: rules clonning. Sorry.");
		}

		$itemsCount = $this->getGrid()->getPaginator()->getItemsPerPage();

		for ($index = 0; $index < $itemsCount ;$index++) {
			$container->addComponent($control = clone $this->controlPrototype, $index);
			$this->addControl($control);
		}

		return $container;
	}



	/**
	 * @return Nette\Forms\IControl
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