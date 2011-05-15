<?php

namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Columns\BaseColumn;
use Kdyby\Components\Grinder\Actions\BaseAction;
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
	 * Renders single Cell
	 *
	 * @param BaseColumn $column
	 * @return void
	 */
	function renderCell(BaseColumn $column);


	/**
	 * Renders one action
	 *
	 * @param BaseAction $action
	 * @return void
	 */
	function renderAction(BaseAction $action);

}