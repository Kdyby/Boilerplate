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
	 * Render datetime
	 * @param Datetime value
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
	 * @param ActionColumn $column
	 * @return string
	 */
	public function renderActionsCell(ActionColumn $column)
	{
		$s = NULL;

		foreach ($column->getActions() as $action) {
			ob_start();
				$action->render();
			$s .= ob_get_clean();
		}

		return $s;
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

		if ($column instanceof ActionColumn) {
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