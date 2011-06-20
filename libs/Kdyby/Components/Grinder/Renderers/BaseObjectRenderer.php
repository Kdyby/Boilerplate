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
use Nette;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 */
abstract class BaseObjectRenderer extends BaseRenderer
{

	/** @var Grid */
	protected $grid;



	/**
	 * Renders the grid
	 *
	 * @param Grid $grid
	 * @return void
	 */
	public function render(Grid $grid)
	{
		$args = func_get_args();
		$this->grid = array_shift($args);

		if (array_filter($args)) {
			$part = array_shift($args);
			echo call_user_func_array(array($this, 'render' . $part), (array)current($args));
			return;
		}

		$s = Html::el('div')->setClass('grid');

		$s->add($this->renderFlashes());

		$s->add($this->renderForm('begin'));
		$s->add($this->renderToolbar(Grid::PLACEMENT_TOP));
		$s->add($this->renderPaginator(Grid::PLACEMENT_TOP));

		if ($this->grid->getModel()->count() > 0) {
			$s->add($this->renderData());

		} else {
			$s->add($this->renderEmptyResult());
		}

		$s->add($this->renderPaginator(Grid::PLACEMENT_BOTTOM));
		$s->add($this->renderToolbar(Grid::PLACEMENT_BOTTOM));
		$s->add($this->renderForm('end'));

		// output
		echo $s;
	}



	/**
	 * @return Html|NULL
	 */
	abstract protected function renderData();



	/**
	 * @return Html|NULL
	 */
	protected function renderFlashes()
	{
		$flashes = Html::el('ul')->setClass('grinder-flashes');

		$flashesId  = $this->grid->getParamId('flash');
		$messages = (array)$this->grid->getPresenter()->getFlashSession()->{$flashesId};
		foreach ($messages as $message) {
			$flash = Html::el('li')->class('flash grinder-flash ' . $message->type);
			$flashes->add($flash->{$message->message instanceof Html ? 'add' : 'setText'}($message->message));
		}

		foreach ($this->grid->getForm()->getErrors() as $error) {
			$flash = Html::el('li')->class('flash grinder-flash error');
			$flashes->add($flash->{$error instanceof Html ? 'add' : 'setText'}($error));
		}

		return count($flashes) ? $flashes : "";
	}



	/**
	 * @param string $placement
	 * @return Html|NULL
	 */
	protected function renderToolbar($placement)
	{
		$actions = $this->grid->getToolbar()->getActions($placement);
		if (count($actions) <= 0) {
			return "";
		}

		$toolbarContainer = Html::el('div')->setClass('grinder-toolbar');

		foreach ($actions as $action) {
			$actionContainer = Html::el('span')->setClass('grinder-toolbar-action');
			$actionContainer->add($this->renderAction($action));
			$toolbarContainer->add($actionContainer);
		}

		return $toolbarContainer;
	}



	/**
	 * @return Html|NULL
	 */
	protected function renderEmptyResult()
	{
		$message = $this->grid->getEmptyResultMessage();

		return Html::el('div')->setClass('grinder-empty')
			->setHtml(Html::el('p')->{$message instanceof Html ? 'add' : 'setText'}($message));
	}



	/**
	 * @param string $partName
	 * @return Html|NULL
	 */
	protected function renderForm($partName = NULL)
	{
		$form = $this->grid->getForm();
		return Html::el()->setHtml($form->getRenderer()->render($form, $partName));
	}



	/**
	 * @return Html|NULL
	 */
	protected function renderPaginator($placement = Grid::PLACEMENT_BOTH)
	{
		$vp = $this->grid->getVisualPaginator();
		if (!in_array($vp->getPlacement(), array($placement, Grid::PLACEMENT_BOTH))) {
			return "";
		}

		ob_start();
			$vp->render();
		return Html::el()->setHtml(ob_get_clean());
	}

}