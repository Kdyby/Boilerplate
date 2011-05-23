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
use Kdyby\Components\Grinder\Grid;
use Nette;



/**
 * @author Filip Procházka
 *
 * @property string $placement
 */
class GridPaginator extends Kdyby\Components\VisualPaginator\VisualPaginator
{

	/** @vat string */
	private $placement = Grid::PLACEMENT_BOTTOM;



	/**
	 * @param string $placement
	 * @return BaseAction
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
		return $this->getPlacement() === self::PLACEMENT_BOTH || $this->getPlacement() === self::PLACEMENT_TOP;
	}



	/**
	 * @return bool
	 */
	public function isOnBottom()
	{
		return $this->getPlacement() === self::PLACEMENT_BOTH || $this->getPlacement() === self::PLACEMENT_BOTTOM;
	}

}