<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Nette\Application\Presenter;
use Nette\Forms\FormContainer;
use Nette\Web\Html;
use Kdyby;
use Kdyby\Components\Grinder\Actions\BaseAction;



/**
 * Grid column
 *
 * @author Filip Procházka
 */
class ActionsColumn extends BaseColumn
{

	/** @var Kdyby\Components\Grinder\Actions\BaseAction */
	private $actions = array();



	/**
	 * @param Kdyby\Components\Grinder\Actions\BaseAction $action
	 * @param string $name
	 * @return Kdyby\Components\Grinder\Actions\BaseAction
	 */
	public function addAction(BaseAction $action, $name = NULL)
	{
		if ($column = $action->getColumn()) {
			throw new \InvalidArgumentException("Action '" . $action->name . "' is already attached to '" . $column->name . "'.");
		}

		if (!$action->getParent()) {
			$this->getGrid()->add($action, $name);
		}

		$action->setColumn($this);
		return $this->actions[] = $action;
	}



	/**
	 * @return array
	 */
	public function getActions()
	{
		return $this->actions;
	}



	/**
	 * @return Nette\Web\Html
	 */
	public function getControl()
	{
		$control = Html::el();

		foreach ($this->getActions() as $action) {
			if (!$action->isVisible()) {
				continue;
			}

			$control->add(' ');
			$control->add($this->getRenderer()->renderAction($action));
		}

		return $control;
	}

}