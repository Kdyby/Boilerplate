<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder;

use Kdyby;
use Nette;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 *
 * @property-read boolean $onTop
 * @property-read boolean $onBottom
 * @property-read \ArrayIterator $topActions
 * @property-read \ArrayIterator $bottomActions
 */
class Toolbar extends Nette\Application\UI\PresenterComponent
{

	/** @var array */
	private $defaults = array(
		'placement' => Grid::PLACEMENT_BOTH,
	);



	/**
	 * @param boolean $need
	 * @return Grid
	 */
	public function getGrid($need = TRUE)
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid', $need);
	}



	/**
	 * @param array $defaults
	 * @return Toolbar
	 */
	public function setDefaults($defaults)
	{
		$this->defaults = $defaults;
		return $this;
	}



	/**
	 * @return array
	 */
	public function getDefaults()
	{
		return $this->defaults;
	}


	/********************* Actions *********************/


	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @return Actions\FormAction
	 */
	public function addButton($name, $caption = NULL, array $options = array())
	{
		return $this->add(new Actions\FormAction($caption), $name, $options);
	}


	/********************* Protection *********************/


	/**
	 * @param IComponent
	 * @throws Nette\InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child)
	{
		parent::validateChildComponent($child);

		if (!$child instanceof Actions\BaseAction) {
			throw new Nette\InvalidStateException("Child component of " . $this->name . " must be instanceof Kdyby\\Components\\Grinder\\Actions\\BaseAction.");
		}
	}



	/**
	 * @param IComponent $component
	 * @param string $name
	 * @param array $options
	 * @return IComponent
	 */
	public function add(IComponent $component, $name, array $options = array())
	{
		$insertBefore = &$options['insertBefore'] ?: NULL;
		unset($options['insertBefore']);

		$name = $this->getGrid()->getComponentSafeName($component, $name);
		$this->addComponent($component, $name, $insertBefore);
		return $this->getGrid()->add($component, NULL, $options);
	}


	/********************* Helpers *********************/


	/**
	 * @return bool
	 */
	public function isOnTop()
	{
		return count($this->getTopActions()) > 0;
	}



	/**
	 * @return bool
	 */
	public function isOnBottom()
	{
		return count($this->getBottomActions()) > 0;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getTopActions()
	{
		return $this->getActions(Grid::PLACEMENT_TOP);
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getBottomActions()
	{
		return $this->getActions(Grid::PLACEMENT_BOTTOM);
	}



	/**
	 * @param string|NULL $placement
	 * @return \ArrayIterator
	 */
	public function getActions($placement = NULL)
	{
		if (!$placement) {
			return $this->getActionComponents();
		}

		$actions = array_filter(iterator_to_array($this->getActionComponents()), function (Actions\BaseAction $action) use ($placement) {
			return $action->getPlacement() === $placement || $action->getPlacement() === Grid::PLACEMENT_BOTH;
		});

		return new \ArrayIterator($actions);
	}



	/**
	 * @return \ArrayIterator
	 */
	protected function getActionComponents()
	{
		return $this->getComponents(FALSE, 'Kdyby\Components\Grinder\Actions\BaseAction');
	}



	/**
	 * Renders actions that are registered into top toolbar
	 */
	public function renderTop()
	{
		$toolbar = Html::el('div')->class('grinder-toolbar grinder-toolbar-top');
		foreach ($this->getTopActions() as $action) {
			$toolbar->add((string)$action);
		}
		echo $toolbar;
	}



	/**
	 * Renders actions that are registered into bottom toolbar
	 */
	public function renderBottom()
	{
		$toolbar = Html::el('div')->class('grinder-toolbar grinder-toolbar-bottom');
		foreach ($this->getTopActions() as $action) {
			$toolbar->add((string)$action);
		}
		echo $toolbar;
	}

}