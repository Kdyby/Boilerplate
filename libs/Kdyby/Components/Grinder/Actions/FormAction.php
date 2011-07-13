<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;
use Nette\Forms\Form;
use Nette\Forms\Controls\SubmitButton;



/**
 * @author Filip Procházka
 */
class FormAction extends BaseAction
{

	/** @var SubmitButton */
	private $control;

	/** @var Grinder\Columns\CheckColumn */
	private $checkColumn;



	/**
	 * @param string $caption
	 */
	public function __construct($caption = NULL)
	{
		parent::__construct();
		$this->control = new SubmitButton($caption);
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Grinder\Grid) {
			return;
		}

		$form = $this->getGrid()->getForm();
		$form->getToolbarContainer()->addComponent($this->control, $this->name);
		$form->onSuccess[] = array($this, 'fireEvents');
	}



	/**
	 * @param Grinder\Columns\CheckColumn $column
	 * @return FormAction
	 */
	public function setCheckColumn(Grinder\Columns\CheckColumn $column)
	{
		$this->checkColumn = $column;
		return $this;
	}



	/**
	 * @return Grinder\Columns\CheckColumn
	 */
	public function getCheckColumn()
	{
		return $this->checkColumn;
	}



	/**
	 * @internal
	 * @return void
	 */
	public function fireEvents()
	{
		$form = $this->getGrid()->getForm();
		if ($form->isSubmitted() === $this->control) {
			if ($this->checkColumn === NULL) {
				return $this->handleClick();
			}

			$this->handleClick(array_keys(array_filter($this->checkColumn->getValues())));
		}
	}



	/**
	 * @return Html
	 */
	public function getControl()
	{
		return $this->control->getControl();
	}



	/**
	 * @return Html
	 */
	public function getLabel()
	{
		return $this->control->getLabel();
	}



	/**
	 * @return Nette\Utils\Html
	 */
	public function getControlPrototype()
	{
		return $this->control->getControlPrototype();
	}

}