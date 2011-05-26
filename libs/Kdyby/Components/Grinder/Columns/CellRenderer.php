<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Columns;

use DateTime;
use Kdyby;
use Nette;
use Nette\Utils\Html;
use Nette\Templating\DefaultHelpers;



/**
 * @author Filip Procházka
 */
abstract class CellRenderer extends Nette\Object
{

	/**
	 * Render boolean
	 * @param bool value
	 */
	public function renderBoolean($value)
	{
		return $value ? "ano" : "ne";
	}



	/**
	 * Render datetime
	 * @param DateTime value
	 * @param string datetime format
	 */
	public function renderDateTime(DateTime $date, $format = 'j.n.Y G:i')
	{
		return $date->format($format);
	}



	/**
	 * @param FormColumn $column
	 * @return Html
	 */
	public function renderFormCell(FormColumn $column)
	{
		// column control -> IFormControl control
		return $column->getControl()->getControl();
	}



	/**
	 * Default cell renderer
	 *
	 * @param BaseColumn $column
	 * @return string|Html Returns sanitized string or safe HTML
	 */
	public function renderCell(BaseColumn $column)
	{
		if ($column instanceof FormColumn || $column instanceof CheckColumn) {
			return $this->renderFormCell($column);
		}

		return $column->getControl();
	}

}