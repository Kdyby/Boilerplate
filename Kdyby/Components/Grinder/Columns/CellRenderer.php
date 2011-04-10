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
	protected function renderBoolean($value)
	{
		return $value ? "ano" : "ne";
	}



	/**
	 * Render datetime
	 * @param Datetime value
	 * @param string datetime format
	 */
	protected function renderDateTime(DateTime $date, $format = 'j.n.Y G:i')
	{
		return $date->format($format);
	}



	/**
	 * @param FormColumn $column
	 * @return Html
	 */
	protected function renderFormCell(FormColumn $column)
	{
		// column control -> IFormControl control
		return $column->getControl()->getControl();
	}



	/**
	 * @param ActionsColumn $column
	 * @return Nette\Web\Html
	 */
	protected function renderActionsCell(ActionsColumn $column)
	{
		return $column->getControl();
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

		if ($column instanceof ActionsColumn) {
			return $this->renderActionsCell($column);
		}

		$value = $column->getValue();

		if (is_bool($value)) {
			return $this->renderBoolean($value);

		} elseif ($value instanceof \DateTime) {
			return $this->renderDateTime($value, $column->dateTimeFormat);
		}

		// other
		return TemplateHelpers::escapeHtml($value);
	}

}