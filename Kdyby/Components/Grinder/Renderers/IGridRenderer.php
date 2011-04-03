<?php

namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Columns\BaseColumn;
use Kdyby\Components\Grinder\Toolbar\BaseAction;
use Nette;




/**
 * @author Filip Procházka
 */
interface IGridRenderer
{

	/**
	 * Renders the grid
	 *
	 * @param Grid $grid
	 * @return void
	 */
	function render(Grid $grid);


	/**
	 * Renders one value from one row
	 *
	 * @param BaseColumn $column
	 * @return void
	 */
	function renderCell(BaseColumn $column);


	/**
	 * Renders one value from one row
	 *
	 * @param BaseAction $action
	 * @return void
	 */
	function renderToolbarAction(BaseAction $action);

}