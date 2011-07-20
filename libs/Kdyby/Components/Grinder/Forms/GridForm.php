<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Forms;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;



/**
 * @author Filip Procházka
 */
class GridForm extends Nette\Application\UI\Form
{

	public function __construct()
	{
		parent::__construct(NULL, NULL);
		$this->addContainer('toolbar');
		$this->addContainer('columns');
		$this->addContainer('ids');

		// Allways - your every-day protection
//		$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof Nette\Application\UI\Presenter) {
			$itemsCount = $this->getGrid()->itemsPerPage;

			for ($index = 0; $index < $itemsCount ;$index++) {
				$this->getComponent('ids')->addHidden($index);
			}
		}

		parent::attached($obj);
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
	 * @return Nette\Forms\Container
	 */
	public function getToolbarContainer()
	{
		return $this->getComponent('toolbar');
	}



	/**
	 * @return array
	 */
	public function getRecordsIds()
	{
		return array_filter($this->getComponent('ids')->getValues());
	}



	/**
	 * @param string $name
	 * @return Nette\Forms\Container
	 */
	public function getColumnContainer($name)
	{
		$columns = $this->getComponent('columns');
		if (!$columns->getComponent($name, FALSE)) {
			$columns->addContainer($name);
		}

		return $columns->getComponent($name);
	}

}