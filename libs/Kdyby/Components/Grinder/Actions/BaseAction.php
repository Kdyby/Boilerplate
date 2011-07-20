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



/**
 * @author Filip Procházka
 *
 * @property-read string $realName
 */
abstract class BaseAction extends Nette\Application\UI\PresenterComponent
{

	/** @var boolean */
	public $handlerPassEntity = FALSE;

	/** @var callback */
	private $handler;

	/** @var string|callable */
	private $visible = TRUE;

	/** @var string|callable */
	private $confirmationQuestion;

	/** @vat string */
	private $placement = Grinder\Grid::PLACEMENT_TOP;



	public function __construct()
	{
		parent::__construct();
		$this->monitor('Kdyby\Components\Grinder\Grid');
	}



	/**
	 * @param boolean $need
	 * @return Grid
	 */
	public function getGrid($need = TRUE)
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid', $need);
	}



	/**
	 * @return string
	 */
	public function getRealName()
	{
		return $this->getGrid()->getComponentRealName($this);
	}



	/**
	 * @param bool|callback visible
	 * @return BaseAction
	 */
	public function setVisible($visible)
	{
		if (!is_bool($visible) && !is_callable($visible) && $visible !== NULL) {
			throw new \InvalidArgumentException("Argument should be callable or boolean.");
		}

		$this->visible = $visible;
		return $this;
	}



	/**
	 * @param mixed row
	 * @return bool
	 */
	public function isVisible()
	{
		$record = $this->getGrid()->getCurrentRecord();
		return is_bool($this->visible) ? $this->visible : call_user_func($this->visible, $record);
	}



	/**
	 * @return callback
	 */
	public function getHandler()
	{
		return $this->handler;
	}



	/**
	 * @param callable handler
	 * @return BaseAction
	 */
	public function setHandler($handler)
	{
		$this->handler = callback($handler);
		return $this;
	}



	/**
	 * @param int $id
	 */
	public function handleClick($id = NULL)
	{
		if (!is_callable($this->getHandler())) {
			throw new Nette\InvalidStateException("Handler for action '" . $this->realName . "' is not callable.");
		}

		$id = $id ?: NULL;
		if ($this->handlerPassEntity === TRUE) {
			if (!$id) {
				throw new Nette\InvalidStateException("Missing argument '\$id' in action '" . $this->realName . "'.");
			}

			$model = $this->getGrid()->getModel();
			$id = is_array($id)
				? $model->getItemsByUniqueIds($id)
				: $model->getItemByUniqueId($id);
		}

		call_user_func($this->getHandler(), $this, $id);
	}



	/**
	 * Set confirmation question
	 * @param string|callback $question
	 * @return BaseAction
	 */
	public function setConfirmationQuestion($question)
	{
		if (!is_string($question) && !is_callable($question) && $question !== NULL) {
			throw new \InvalidArgumentException("Confirmation question should be callable or string.");
		}

		throw new Nette\NotImplementedException("sorry bro");
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
	public function setPlacement($placement)
	{
		if ($this->getParent() !== $this->getGrid()->getToolbar()) {
			throw new Nette\InvalidStateException("Action is not attached to toolbar.");
		}

		$this->placement = $placement;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getPlacement()
	{
		return $this->placement;
	}



	/**
	 * @return Nette\Utils\Html|NULL
	 */
	public function getLabel()
	{
		return NULL;
	}



	/**
	 * @return Nette\Utils\Html|NULL
	 */
	abstract public function getControl();



	/**
	 * @return void
	 */
	public function render()
	{
		echo (string)$this->getControl();
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		try {
			return (string)$this->getControl();
		} catch (\Exception $e) {
			Nette\Diagnostics\Debugger::log($e);
		}
	}

}