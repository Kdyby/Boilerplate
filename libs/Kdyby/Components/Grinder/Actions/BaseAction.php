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
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Columns\ActionsColumn;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;
use Nette;



/**
 * @author Filip Procházka
 */
abstract class BaseAction extends Kdyby\Components\Grinder\GridComponent
{

	/** @var ActionsColumn */
	private $column;

	/** @var string|callable */
	private $visible = TRUE;

	/** @var boolean */
	private $ajax;

	/** @var string|callable */
	private $confirmationQuestion;

	/** @vat string */
	private $toolbarPlacement = Grid::PLACEMENT_TOP;



	/**
	 * @param ActionsColumn $column
	 * @return BaseAction
	 */
	public function setColumn(ActionsColumn $column)
	{
		$this->column = $column;
		return $this;
	}



	/**
	 * @return ActionsColumn
	 */
	public function getColumn()
	{
		return $this->column;
	}



	/**
	 * Set visible
	 * @param bool|callback visible
	 * @return BaseAction
	 */
	public function setVisible($visible)
	{
		if (!is_bool($visible) && !is_callable($visible)) {
			throw new \InvalidArgumentException("Argument should be callable or boolean.");
		}

		$this->visible = $visible;
		return $this;
	}



	/**
	 * Is button visible
	 * @param mixed row
	 * @return bool
	 */
	public function isVisible()
	{
		$record = $this->getGrid()->getCurrentRecord();
		return is_bool($this->visible) ? $this->visible : call_user_func($this->visible, $record);
	}



	/**
	 * Set ajax mode
	 * @param bool ajax
	 * @return BaseAction
	 */
	public function setAjax($ajax)
	{
		$this->ajax = (bool) $ajax;
		return $this;
	}



	/**
	 * Is ajax?
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->ajax;
	}



	/**
	 * Set confirmation question
	 * @param string|callback $question
	 * @return BaseAction
	 */
	public function setConfirmationQuestion($question)
	{
		if (!is_string($question) && !is_callable($question)) {
			throw new \InvalidArgumentException("Confirmation question should be callable or string.");
		}

		$this->confirmationQuestion = $question;
		return $this;
	}



	/**
	 * Get confirmation question
	 * @param \Iterator $iterator
	 * @return string|null
	 */
	public function getConfirmationQuestion(\Iterator $iterator)
	{
		if (is_callable($this->confirmationQuestion)) {
			return call_user_func($this->confirmationQuestion, $iterator, $iterator->current());
		}

		return $this->confirmationQuestion;
	}



	/**
	 * @param string $placement
	 * @return BaseAction
	 */
	public function setToolbarPlacement($placement)
	{
		if ($this->getParent() !== $this->getGrid()->getToolbar()) {
			throw new Nette\InvalidStateException("Action is not attached to toolbar.");
		}

		$this->toolbarPlacement = $placement;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getToolbarPlacement()
	{
		return $this->toolbarPlacement;
	}



	/**
	 * @return Nette\Forms\ISubmitterControl|Nette\Utils\Html
	 */
	abstract public function getControl();



	/**
	 * @return void
	 */
	public function render()
	{
		echo call_user_func(array($this->renderer, 'renderAction'), $this);
	}

}