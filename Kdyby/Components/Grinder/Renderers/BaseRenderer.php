<?php

namespace Kdyby\Components\Grinder\Renderers;

use Kdyby;
use Kdyby\Components\Grinder\Grid;
use Kdyby\Components\Grinder\Columns\CellRenderer;
use Kdyby\Components\Grinder\Toolbar\BaseAction;
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
		$s = Html::el('div')
			->setClass('grid');

		// flash messages
		$s->add($this->renderFlashes($grid));

		// todo: render filters

		// form begin
		$s->add($this->renderForm($grid, 'begin'));

		// toolbar
		$s->add($this->renderToolbar($grid));

		// paginator
		$s->add($grid->hasTopPaginator() ? $this->renderPaginator($grid) : "");

		if ($grid->getModel()->count() > 0) {
			$s->add($this->renderData($grid));

		} else {
			$s->add($this->renderEmptyResults());
		}

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
		$flashes = Html::el('div')
			->setClass('grinder-flashes');

		// the only reliable source of flash messages :-/
		foreach ($grid->template->flashes as $flash) {
			$flash = Html::el('span')
				->addClass('grinder-flash')
				->addClass($flash->type);

			if ($flash->message instanceof Html) {
				$flash->add($flash->message);

			} else {
				$flash->setText($flash->message);
			}

			$flashes->add($flash);
		}

		foreach ($grid->getForm()->getErrors() as $error) {
			$flash = Html::el('span')
				->addClass('grinder-flash')
				->addClass('error');

			if ($error instanceof Html) {
				$flash->add($error);

			} else {
				$flash->setText($error);
			}

			$flashes->add($flash);
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

		$toolbarContainer = Html::el('div')
			->setClass('grinder-toolbar');

		foreach ($grid->getToolbar() as $action) {
			$actionContainer = Html::el('span')
				->setClass('grinder-toolbar-action');

			$actionContainer->add($this->renderToolbarAction($action));
			$toolbarContainer->add($actionContainer);
		}

		return $toolbarContainer;
	}



	/**
	 * @param BaseAction $action
	 * @return Html|NULL
	 */
	public function renderToolbarAction(BaseAction $action)
	{
		if ($action instanceof SelectAction) {

			// etc?
			$action->add($button->getLabel());
			$action->add($button->getControl());
		}

		// Html representation of IFormControl
		return $action->getControl()->getControl();
	}



	/**
	 * @return Html|NULL
	 */
	public function renderEmptyResults()
	{
		return Html::el('div')
			->setClass('grinder-empty')
			->setHtml('<p>Nebyly nalezeny žádné záznamy</p>');
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
	abstract public function renderItem(Grid $grid, \Iterator $iterator);



	/**
	 * @param Grid $grid
	 * @param string $partName
	 * @return Html|NULL
	 */
	public function renderForm(Grid $grid, $partName)
	{
		$form = $grid->getForm();

		ob_start();
			$form->render($partName);
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

}