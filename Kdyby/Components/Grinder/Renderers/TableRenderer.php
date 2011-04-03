<?php


namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
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

		$table->add(Html::el('thead')->add($head = Html::el('tr')));

		foreach ($grid->getColumns() as $column) {
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

			$head->add(Html::el('th')->add($header));
		}

		if ($grid->hasActions()) {
			$head->add(Html::el('th')); // column for actions
		}

		$table->add($body = Html::el('tbody'));

		foreach ($iterator = $grid->getIterator() as $record) {
			$body->add($this->renderItem($grid, $iterator));
		}

		return $table;
	}



	/**
	 * @param Grid $grid
	 * @param \Iterator $iterator
	 * @return Html|NULL
	 */
	public function renderItem(Grid $grid, \Iterator $iterator)
	{
		$item = Html::el('tr')
			->addClass($grid->getRowHtmlClass($iterator));

		foreach ($grid->getColumns() as $column) {
			$cell = Html::el('td');

			ob_start();
				$column->render();
			$cell->setHtml(ob_get_clean());

			$item->add($cell);
		}

		if ($grid->hasActions()) {
			$actions = Html::el('td')
				->setClass('grinder-actions');

			foreach ($grid->getActions() as $action) {
				ob_start();
					$action->render();
				$actions->add(Html::el()->setHtml(ob_get_clean()));
			}

			$item->add($actions);
		}

		return count($item) ? $item : "";
	}

}