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

}