<?php

namespace Kdyby\Components\Grinder\Columns;

use DateTime;
use Kdyby;
use Nette;
use Nette\Templates\TemplateHelpers;



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
	 * Render date
	 * @param Datetime value
	 */
	public function renderDate(DateTime $date)
	{
		return $this->renderDateTime($date, 'j.n.Y');
	}



	/**
	 * Render time
	 * @param Datetime value
	 */
	public function renderTime(DateTime $date)
	{
		return $this->renderDateTime($date, 'G:i');
	}



	/**
	 * Render datetime
	 * @param Datetime value
	 * @param string datetime format
	 */
	public function renderDateTime(DateTime $date, $format = 'j.n.Y G:i')
	{
		return $date->format($format);
	}



	/**
	 * Default cell renderer
	 * 
	 * @param Column $column
	 * @return string|Html
	 */
	public function renderCell(BaseColumn $column)
	{
		if ($column instanceof FormColumn || $column instanceof CheckColumn) {
			// column control -> IFormControl control
			return $column->getControl()->getControl();
		}

		$value = $column->getValue();

		if (is_bool($value)) {
			return $this->renderBoolean($value);

		} elseif ($value instanceof \DateTime) {
			return $this->renderDateTime($value);		
		}

		// other
		return TemplateHelpers::escapeHtml($value);
	}


}