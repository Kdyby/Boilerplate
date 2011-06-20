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
use Kdyby\Components\Grinder\Actions\BaseAction;
use Kdyby\Components\Grinder\Columns\BaseColumn;
use Kdyby\Components\Grinder\Columns\CellRenderer;
use Nette;
use Nette\Utils\Html;



/**
 * @author Filip Procházka
 */
abstract class BaseRenderer extends CellRenderer
{

	/**
	 * @return Html
	 */
	public function renderFilters()
	{
		$form = $this->grid->getFilters()->getForm();
		return Html::el()->setHtml($form->__toString());
	}



	/**
	 * @param BaseAction $action
	 * @return Html|NULL
	 */
	public function renderAction(BaseAction $action)
	{
		if ($action instanceof SelectAction) {
			throw new Nette\NotImplementedException;

			// etc?
			$action->add($button->getLabel());
			$action->add($button->getControl());
		}

		$control = $action->getControl();
		return $control instanceof Html ? $control : $control->getControl();
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