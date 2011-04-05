<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Nette\Forms\FormContainer;
use Kdyby;
use Kdyby\Components\Grinder\Grid;



/**
 * Grid column
 *
 * @author Filip Procházka
 */
class CheckColumn extends FormColumn
{

	public function __construct()
	{
		parent::__construct(new Nette\Forms\Checkbox);
	}



	/**
	 * @param FormContainer $container
	 * @return FormContainer
	 */
	protected function buildControls(FormContainer $container)
	{
		$container = parent::buildControls($container);
		$itemsIdsContainer = $container->addContainer('ids');

		foreach ($container->getComponents() as $checkbox) {
			$itemsIdsContainer->addHidden($checkbox->name);
		}

		return $container;
	}



	/**
	 * @return Nette\Forms\IFormControl
	 */
	public function getControl()
	{
		$control = parent::getControl();
		$grid = $this->getGrid();

		$itemsIdsContainer = $this->getContainer()->getComponent('ids');
		$identifier = $grid->getModel()->getUniqueId($grid->getCurrentRecord());

		if ($identifier) {
			$itemsIdsContainer->getComponent($control->name)->setValue($identifier);
		}

		return $control;
	}



	/**
	 * @return array
	 */
	public function getChecked()
	{
		$keys = array_keys(array_filter($this->getValues()));
		return $this->getGrid()->getModel()->getItemsByUniqueIds($keys);
	}



	/**
	 * @return array
	 */
	public function getValues()
	{
		$itemsIdsContainer = $this->getContainer()->getComponent('ids');

		$values = array();
		foreach ($this->getControls() as $control) {
			$id = $itemsIdsContainer[$control->name]->value;

			if ($id) {
				$values[$id] = $control->value;
			}
		}

		return $values;
	}

}