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
use Nette\Application\UI\Presenter;



/**
 * @author Filip Procházka
 *
 * @property array $allowedSteps
 * @property string $placement
 * @property-read boolean $onTop
 * @property-read boolean $onBottom
 */
class GridPaginator extends Kdyby\Components\VisualPaginator\ComponentPaginator
{

	/** @persistent int */
	public $itemsPerPage = 20;

	/** @var array */
	private $allowedSteps = array(20, 50, 100, 'all');

	/** @var string */
	private $placement = Grid::PLACEMENT_BOTTOM;



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Presenter) {
			return;
		}

		$itemsPerPage = in_array($this->itemsPerPage, $this->getAllowedSteps()) ? $this->itemsPerPage : 20;
		if ($itemsPerPage !== 'all') {
			$this->getPaginator()->setItemsPerPage($itemsPerPage);

		} else {
			$this->getGrid()->page = 1;
		}
	}



	/**
	 * @param array
	 * @return void
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);
		$this->setPage($this->getGrid()->page);
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
	 * @param array $steps
	 * @return GridPaginator
	 */
	public function setAllowedSteps(array $steps)
	{
		$this->allowedSteps = $steps;
		return $this;
	}



	/**
	 * @return array
	 */
	public function getAllowedSteps()
	{
		return $this->allowedSteps;
	}



	/**
	 * @param string $placement
	 * @return GridPaginator
	 */
	public function setPlacement($placement)
	{
		$this->placement = $placement;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getPlacement()
	{
		return $this->placement;
	}



	/**
	 * @return bool
	 */
	public function isOnTop()
	{
		return $this->getPlacement() === Grid::PLACEMENT_BOTH || $this->getPlacement() === Grid::PLACEMENT_TOP;
	}



	/**
	 * @return bool
	 */
	public function isOnBottom()
	{
		return $this->getPlacement() === Grid::PLACEMENT_BOTH || $this->getPlacement() === Grid::PLACEMENT_BOTTOM;
	}



	/**
	 * Renders upper paginator
	 */
	public function renderTop()
	{
		if ($this->isOnTop()) {
			$this->render();
		}
	}



	/**
	 * Renders bottom paginator
	 */
	public function renderBottom()
	{
		if ($this->isOnTop()) {
			$this->render();
		}
	}

}