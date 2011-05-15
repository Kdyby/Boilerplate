<?php

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