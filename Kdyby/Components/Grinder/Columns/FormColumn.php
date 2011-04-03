<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Kdyby;



/**
 * Grid column
 *
 * @author Filip Procházka
 * @license MIT
 */
class FormColumn extends BaseColumn
{

	/** @var Nette\Forms\IFormControl */
	private $controlPrototype;

	/** @var string */
	private $columnName;

	/** @var array */
	private $controls = array();



	/**
	 * @param Nette\Forms\IFormControl $control
	 */
	public function __construct(Nette\Forms\IFormControl $control, $columnName = 'id')
	{
		if ($control->parent) {
			throw new \InvalidArgumentException("Control can't be attached.");
		}

		$this->monitor('Nette\Application\Presenter');

		$this->controlPrototype = $control;
		$this->columnName = $columnName;
	}



	/**
	 * @param Nette\Forms\IFormControl $control
	 */
	public function setControlPrototype(Nette\Forms\IFormControl $control)
	{
		$this->controlPrototype = $control;
	}



	/**
	 * @param Nette\ComponentContainer $obj
	 * @return void
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Kdyby\Application\Presenter) {
			$this->buildControls($obj);
		}
	}



	/**
	 * @param Kdyby\Application\Presenter $presenter
	 * @return void
	 */
	protected function buildControls(Kdyby\Application\Presenter $presenter)
	{
		if ($this->controlPrototype === NULL) {
			throw new \InvalidStateException("Control prototype cannot be null.");
		}

		if ($this->controlPrototype->parent) {
			throw new \InvalidStateException("Control can't be attached.");
		}

		if ($this->controlPrototype->getRules()->getIterator()->count() > 0) {
			throw new \NotSupportedException("Bug: rules clonning. Sorry.");
		}

		$grid = $this->getGrid();
		
	}



	/**
	 * @return Nette\Forms\IFormControl
	 */
	public function getControl()
	{
		$data = $this->getGrid()->getCurrentRecord();
		$form = $this->getGrid()->getComponent('form');

		$control = clone $this->controlPrototype;
		$form[$this->name][$data[$this->columnName]] = $control;
		$this->controls[$data[$this->columnName]] = $control;

		return $control;
	}



	/**
	 * @return array
	 */
	public function getValues()
	{
		$values = array();
		foreach ($this->controls as $id => $control) {
			$values[$id] = $control->value;
		}

		return $values;
	}



	/**
	 * Render cell
	 */
	public function renderCell()
	{
		echo call_user_func($this->renderer, $this);
	}

}