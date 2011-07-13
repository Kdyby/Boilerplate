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



/**
 * @author Filip Procházka
 *
 * @property string $placement
 * @property-read boolean $onTop
 * @property-read boolean $onBottom
 */
class GridPaginator extends Kdyby\Components\VisualPaginator\ComponentPaginator
{

	/** @var string */
	private $placement = Grid::PLACEMENT_BOTTOM;



	/**
	 * @param boolean $need
	 * @return Grid
	 */
	public function getGrid($need = TRUE)
	{
		return $this->lookup('Kdyby\Components\Grinder\Grid', $need);
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



	public function renderTop()
	{
		if ($this->isOnTop()) {
			$this->render();
		}
	}



	public function renderBottom()
	{
		if ($this->isOnTop()) {
			$this->render();
		}
	}

}