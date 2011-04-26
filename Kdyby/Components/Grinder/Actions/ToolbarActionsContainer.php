<?php

namespace Kdyby\Components\Grinder\Actions;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Nette;
use Nette\ComponentModel\IComponent;



/**
 * @author Filip Procházka
 */
class ToolbarActionsContainer extends Nette\ComponentModel\Container
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
			return $this->getComponents();
		}

		$actions = array();
		foreach ($this->getComponents() as $action) {
			if ($action->getToolbarPlacement() === $placement) {
				$actions[] = $action;
			}
		}
	
		return new \ArrayIterator($actions);
	}

}