<?php


namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Columns\BaseColumn;
use Kdyby\Components\Grinder\Columns\Renderer;
use Nette;
use Nette\Web\Html;



/**
 * @author Filip Procházka
 */
class TableRenderer extends BaseRenderer
{

	/**
	 * @param Grid $grid
	 * @return Html|NULL
	 */
	public function renderData(Grid $grid)
	{
		$table = Html::el('table')->setClass('grinder-table');

		// headers
		$table->add(Html::el('thead')->add($head = Html::el('tr')));

		foreach ($grid->getColumns() as $column) {
			$head->add(Html::el('th')->add($this->renderDataHeader($grid, $column)));
		}

		if ($grid->hasActions()) {
			$head->add(Html::el('th')); // column for actions
		}

		// body
		$table->add($body = Html::el('tbody'));

		foreach ($iterator = $grid->getIterator() as $record) {
			$body->add($this->renderDataItem($grid, $iterator));
		}

		return $table;
	}



	/**
	 * @param Grid $grid
	 * @param BaseColumn $column
	 * @return Html|NULL
	 */
	public function renderDataHeader(Grid $grid, BaseColumn $column)
	{
		$header = Html::el('span');
		$caption = $column->getCaption();

		if ($column->isSortable()) {
			$link = Html::el('a')
				->setHref($grid->link('sort!', $this->getColumnSortingArgs($column)))
				->{$caption instanceof Html ? 'add' : 'setText'}($caption);

			$header->addClass('grinder-sortable')->add($link);

		} else {
			$header->{$caption instanceof Html ? 'add' : 'setText'}($caption);
		}

		return $header;
	}



	/**
	 * @param Grid $grid
	 * @param \Iterator $iterator
	 * @return Html|NULL
	 */
	public function renderDataItem(Grid $grid, \Iterator $iterator)
	{
		$item = Html::el('tr')->addClass($grid->getRowHtmlClass($iterator));

		foreach ($grid->getColumns() as $column) {
			$cell = Html::el('td')->addClass($column->getCellHtmlClass($iterator));

			ob_start();
				$column->render();
			$item->add($cell->setHtml(ob_get_clean()));
		}

		if ($grid->hasActions()) {
			$actions = Html::el('td')->addClass('grinder-actions');

			foreach ($grid->getActions() as $action) {
				$actions->add($this->renderAction($action));
			}

			$item->add($actions);
		}

		return count($item) ? $item : "";
	}

}