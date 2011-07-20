<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

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
			throw new Nette\InvalidArgumentException("Control " . $control->name . " can't be attached.");
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
	 * @param Nette\ComponentModel\Container $obj
	 * @return void
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Presenter) {
			return;
		}

		$this->buildControls($this->getContainer());
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
	 * @return Container
	 */
	protected function getContainer()
	{
		return $this->getGrid()->getForm()->getColumnContainer($this->name);
	}



	/**
	 * @param Container $container
	 * @return Container
	 */
	protected function buildControls(Container $container)
	{
		if ($this->controlPrototype->parent) {
			throw new Nette\InvalidStateException("Control can't be attached.");
		}

		if ($this->controlPrototype->getRules()->getIterator()->count() > 0) {
			throw new Nette\NotSupportedException("Bug: rules clonning. Sorry.");
		}

		$itemsCount = $this->getGrid()->itemsPerPage;

		for ($index = 0; $index < $itemsCount ;$index++) {
			$container->addComponent($control = clone $this->controlPrototype, $index);
			$this->addControl($control);
		}

		return $container;
	}



	/**
	 * @return Nette\Utils\Html
	 */
	public function getControl()
	{
		return $this->getCurrentControl()->getControl();
	}



	/**
	 * @return Nette\Forms\IControl
	 */
	public function getCurrentControl()
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
		foreach ($this->controls as $control) {
			$values[$control->name] = $control->value;
		}

		return $values;
	}

}