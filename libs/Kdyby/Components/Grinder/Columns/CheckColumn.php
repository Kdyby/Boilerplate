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
use Nette\Forms\Container;
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
		parent::__construct(new Nette\Forms\Controls\Checkbox);
	}



	/**
	 * @param Container $container
	 * @return Container
	 */
	protected function buildControls(Container $container)
	{
		$container = parent::buildControls($container);
		$itemsIdsContainer = $container->addContainer('ids');

		foreach ($container->getComponents() as $checkbox) {
			$itemsIdsContainer->addHidden($checkbox->name);
		}

		return $container;
	}



	/**
	 * @return Nette\Forms\IControl
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
		return $keys ? $this->getGrid()->getModel()->getItemsByUniqueIds($keys) : array();
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