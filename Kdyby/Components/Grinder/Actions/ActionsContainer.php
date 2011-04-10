<?php

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Nette;
use Nette\IComponent;



/**
 * @author Filip Procházka
 */
class ActionsContainer extends Nette\ComponentContainer
{

	/**
	 * @param IComponent
	 * @throws \InvalidStateException
	 */
	protected function validateChildComponent(IComponent $child)
	{
		parent::validateChildComponent($child);

		if (!$child instanceof BaseAction) {
			throw new \InvalidStateException("Child component of " . $this->name . " must be instanceof Kdyby\\Components\\Grinder\\Actions\\BaseAction.");
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