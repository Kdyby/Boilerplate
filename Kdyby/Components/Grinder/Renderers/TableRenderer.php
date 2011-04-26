<?php


namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Columns\BaseColumn;
use Kdyby\Components\Grinder\Columns\Renderer;
use Nette;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 */
class TableRenderer extends BaseRenderer
{

	/**
	 * @return Html|NULL
	 */
	protected function renderData()
	{
		$table = Html::el('table')->setClass('grinder-table');

		// headers
		$table->add(Html::el('thead')->add($head = Html::el('tr')));

		foreach ($this->grid->getColumns() as $column) {
			$head->add(Html::el('th')->add($this->renderDataHeader($column)));
		}

		$unattachedActions = $this->grid->getActionsContainer()->getUnattachedActions();
		if (count($unattachedActions) > 0) {
			$head->add(Html::el('th')); // column for actions
		}

		// body
		$table->add($body = Html::el('tbody'));

		foreach ($iterator = $this->grid->getIterator() as $record) {
			$body->add($this->renderDataItem($iterator, $unattachedActions));
		}

		return $table;
	}



	/**
	 * @param BaseColumn $column
	 * @return Html|NULL
	 */
	private function renderDataHeader(BaseColumn $column)
	{
		$header = Html::el('span');
		$caption = $column->getCaption();

		if ($column->isSortable()) {
			$link = Html::el('a')
				->setHref($this->grid->link('sort!', $this->getColumnSortingArgs($column)))
				->{$caption instanceof Html ? 'add' : 'setText'}($caption);

			$header->addClass('grinder-sortable')->add($link);

		} else {
			$header->{$caption instanceof Html ? 'add' : 'setText'}($caption);
		}

		return $header;
	}



	/**
	 * @param \Iterator $iterator
	 * @param \Iterator $unattachedActions
	 * @return Html|NULL
	 */
	private function renderDataItem(\Iterator $iterator, \Iterator $unattachedActions)
	{
		$item = Html::el('tr')->addClass($this->grid->getRowHtmlClass($iterator));

		foreach ($this->grid->getColumns() as $column) {
			$cell = Html::el('td')->addClass($column->getCellHtmlClass($iterator));

			ob_start();
				$column->render();
			$item->add($cell->setHtml(ob_get_clean()));
		}

		if (count($unattachedActions) > 0) {
			$actions = Html::el('td')->addClass('grinder-actions');

			foreach ($unattachedActions as $action) {
				if (!$action->isVisible()) {
					continue;
				}

				$actions->add(' ');
				$actions->add($this->renderAction($action));
			}

			$item->add($actions);
		}

		return count($item) ? $item : "";
	}

}