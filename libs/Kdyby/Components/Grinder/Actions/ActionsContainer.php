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
use Nette;
use Nette\ComponentModel\IComponent;



/**
 * @author Filip Procházka
 */
class ActionsContainer extends Nette\ComponentModel\Container
{

	/**
	 * @param IComponent
	 * @throws Nette\InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child)
	{
		parent::validateChildComponent($child);

		if (!$child instanceof BaseAction) {
			throw new Nette\InvalidStateException("Child component of " . $this->name . " must be instanceof Kdyby\\Components\\Grinder\\Actions\\BaseAction.");
		}
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getActions()
	{
		return $this->getComponents();
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getUnattachedActions()
	{
		$actions = array();
		foreach ($this->getActions() as $action) {
			if (!$action->getColumn()) {
				$actions[] = $action;
			}
		}

		return new \ArrayIterator($actions);
	}

}