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
		$table = Html::el('table')
			->setClass('grinder-table');

		// headers
		$table->add(Html::el('thead')
			->add($head = Html::el('tr')));

		foreach ($grid->getColumns() as $column) {
			$head->add(Html::el('th')->add($this->renderDataHeader($grid, $column)));
		}

//		if ($grid->hasActions()) {
//			$head->add(Html::el('th')); // column for actions
//		}

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

		if ($column->isSortable()) {
			$header->addClass('grinder-sortable');

			$link = Html::el('a');

			if ($column->getSorting() === NULL) {
				$link->setHref($grid->link('sort!', $column->getName(), 'asc'));

			} elseif ($column->getSorting() === 'asc') {
				$link->setHref($grid->link('sort!', $column->getName(), 'desc'));

			} elseif ($column->getSorting() === 'desc') {
				$link->setHref($grid->link('sort!', NULL, NULL));
			}

			$link->setText($column->getCaption());
			$header->add($link);

		} else {
			$header->setText($column->getCaption());
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
		$item = Html::el('tr')
			->addClass($grid->getRowHtmlClass($iterator));

		foreach ($grid->getColumns() as $column) {
			$cell = Html::el('td')
				->addClass($column->getCellHtmlClass($iterator));

			ob_start();
				$column->render();
			$cell->setHtml(ob_get_clean());

			$item->add($cell);
		}

		return count($item) ? $item : "";
	}

}