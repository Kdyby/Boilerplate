<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

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