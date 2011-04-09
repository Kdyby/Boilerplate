<?php

namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Actions\BaseAction;
use Kdyby\Components\Grinder\Columns\BaseColumn;
use Kdyby\Components\Grinder\Columns\CellRenderer;
use Nette;
use Nette\Forms\Button;
use Nette\Web\Html;




/**
 * @author Filip Procházka
 */
abstract class BaseRenderer extends CellRenderer implements IGridRenderer
{

	/**
	 * Renders the grid
	 *
	 * @param Grid $grid
	 * @return void
	 */
	public function render(Grid $grid)
	{
		$s = Html::el('div')->setClass('grid');

		// flash messages
		$s->add($this->renderFlashes($grid));

		// todo: render filters

		// form begin
		$s->add($this->renderForm($grid, 'begin'));

		// toolbar
		$s->add($grid->hasTopToolbar() ? $this->renderToolbar($grid) : "");

		// paginator
		$s->add($grid->hasTopPaginator() ? $this->renderPaginator($grid) : "");

		if ($grid->getModel()->count() > 0) {
			$s->add($this->renderData($grid));

		} else {
			$s->add($this->renderEmptyResults($grid));
		}

		// toolbar
		$s->add($grid->hasBottomToolbar() ? $this->renderToolbar($grid) : "");

		// paginator
		$s->add($grid->hasBottomPaginator() ? $this->renderPaginator($grid) : "");

		// form end
		$s->add($this->renderForm($grid, 'end'));

		// output
		echo $s;
	}



	/**
	 * @param Grid $grid
	 * @return Html|NULL
	 */
	public function renderFlashes(Grid $grid)
	{
		$flashes = Html::el('div')->setClass('grinder-flashes');

		$flashesId  = $grid->getParamId('flash');
		$messages = (array)$grid->getPresenter()->getFlashSession()->{$flashesId};
		foreach ($messages as $message) {
			$flash = Html::el('span')->addClass('grinder-flash')->addClass($message->type);
			$flashes->add($flash->{$message->message instanceof Html ? 'add' : 'setText'}($message->message));
		}

		foreach ($grid->getForm()->getErrors() as $error) {
			$flash = Html::el('span')->addClass('grinder-flash')->addClass('error');
			$flashes->add($flash->{$error instanceof Html ? 'add' : 'setText'}($error));
		}

		return count($flashes) ? $flashes : "";
	}



	/**
	 * @param Grid $grid
	 * @return Html|NULL
	 */
	public function renderToolbar(Grid $grid)
	{
		if (!$grid->hasToolbar()) {
			return "";
		}

		$toolbarContainer = Html::el('div')->setClass('grinder-toolbar');

		foreach ($grid->getToolbar() as $action) {
			$actionContainer = Html::el('span')->setClass('grinder-toolbar-action');
			$actionContainer->add($this->renderAction($action));
			$toolbarContainer->add($actionContainer);
		}

		return $toolbarContainer;
	}



	/**
	 * @param BaseAction $action
	 * @return Html|NULL
	 */
	public function renderAction(BaseAction $action)
	{
		if ($action instanceof SelectAction) {
			throw new \NotImplementedException;

			// etc?
			$action->add($button->getLabel());
			$action->add($button->getControl());
		}

		$control = $action->getControl();
		return $control instanceof Html ? $control : $control->getControl();
	}



	/**
	 * @return Html|NULL
	 */
	public function renderEmptyResults(Grid $grid)
	{
		$message = $grid->getEmptyResultMessage();

		return Html::el('div')->setClass('grinder-empty')
			->setHtml(Html::el('p')->{$message instanceof Html ? 'add' : 'setText'}($message));
	}



	/**
	 * @param Grid $grid
	 * @return Html|NULL
	 */
	abstract public function renderData(Grid $grid);



	/**
	 * @param Grid $grid
	 * @param \Iterator $iterator
	 * @return Html|NULL
	 */
	abstract public function renderDataHeader(Grid $grid, BaseColumn $column);



	/**
	 * @param Grid $grid
	 * @param \Iterator $iterator
	 * @return Html|NULL
	 */
	abstract public function renderDataItem(Grid $grid, \Iterator $iterator);



	/**
	 * @param Grid $grid
	 * @param string $partName
	 * @return Html|NULL
	 */
	public function renderForm(Grid $grid, $partName)
	{
		ob_start();
			$grid->getForm()->render($partName);
		return Html::el()->setHtml(ob_get_clean());
	}



	/**
	 * @param Grid $grid
	 * @return Html|NULL
	 */
	public function renderPaginator(Grid $grid)
	{
		ob_start();
			$grid->getComponent('vp')->render();
		return Html::el()->setHtml(ob_get_clean());
	}



	/**
	 * @param BaseColumn $column
	 * @return array
	 */
	public static function getColumnSortingArgs(BaseColumn $column)
	{
		$sorting = array(
			"" => array($column->name, 'asc'),
			'asc' => array($column->name, 'desc'),
			'desc' => array(NULL, NULL)
		);

		return isset($sorting[(string)$column->sorting]) ? $sorting[(string)$column->sorting] : $sorting[""];
	}

}