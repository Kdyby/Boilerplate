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

		foreach ($this->getControls() as $control) {
			$control->getControlPrototype()->class('grinder-row-check');
		}

		return $container;
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
		$ids = $this->getGrid()->getForm()->getRecordsIds();
		return array_combine($ids, array_slice($this->getValues(), 0, count($ids)));
	}

}