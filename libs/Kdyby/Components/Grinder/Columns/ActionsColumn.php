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
use Nette\ComponentModel\IComponent;
use Nette\Utils\Html;
use Kdyby;
use Kdyby\Components\Grinder;
use Kdyby\Components\Grinder\Actions;



/**
 * @author Filip Procházka
 *
 * @property-read \ArrayIterator $actions
 * @property-read Actions\BaseAction $first
 */
class ActionsColumn extends BaseColumn
{

	/** @var bool */
	public $reversed = FALSE;



	/**
	 * @return \ArrayIterator
	 */
	public function getActions()
	{
		return $this->getComponents(FALSE, 'Kdyby\Components\Grinder\Actions\BaseAction');
	}



	/**
	 * @return Actions\BaseAction
	 */
	public function getFirst()
	{
		return reset(iterator_to_array($this->getActions()));
	}



	/**
	 * @return Html
	 */
	public function getControl()
	{
		$actions = iterator_to_array($this->getActions());
		if ($this->reversed === TRUE) {
			$actions = array_reverse($actions);
		}

		$control = Html::el();
		foreach ($actions as $action) {
			if (!$action->isVisible()) {
				continue;
			}

			$control->add(' ');
			$control->add((string)$action);
		}

		return $control;
	}


	/********************* Actions *********************/


	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @return Actions\LinkAction
	 */
	public function addLink($name, $caption = NULL, array $options = array())
	{
		$options = array('caption' => $caption) + $options;
		return $this->add(new Actions\LinkAction, $name, $options);
	}



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

}