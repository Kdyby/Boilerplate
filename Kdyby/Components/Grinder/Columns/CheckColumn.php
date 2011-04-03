<?php

namespace Kdyby\Components\Grinder\Columns;

use Nette;
use Nette\Forms\FormContainer;
use Kdyby;
use Kdyby\Application\Presenter;
use Kdyby\Components\Grinder\Grid;



/**
 * Grid column
 *
 * @author Filip Procházka
 * @license MIT
 */
class CheckColumn extends BaseColumn
{

	/** @var array */
	private $controls = array();



	public function __construct()
	{
//		$this->monitor('Kdyby\Components\Grinder\Grid');
		$this->monitor('Kdyby\Application\Presenter');
	}



	/**
	 * @param Nette\ComponentContainer $obj
	 * @return void
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Presenter) {
			return;
		}

		$form = $this->getGrid()->getForm();
		$container = $form->addContainer($this->name);
		$this->buildControls($container);
	}



	/**
	 * @param FormContainer $container
	 * @return void
	 */
	protected function buildControls(FormContainer $container)
	{
		$grid = $this->getGrid();
		$itemsCount = $grid->getPaginator()->getItemsPerPage();

		$itemsIds = $container->addContainer('ids');

		for ($i = 0; $i < $itemsCount ;$i++) {
			$this->controls[$i] = $container->addCheckBox($i);
			$itemsIds->addHidden($i);
		}
	}



	/**
	 * @return Nette\Forms\IFormControl
	 */
	public function getControl()
	{
		$grid = $this->getGrid();

		$index = $grid->getCurrentIndex();
		$record = $grid->getCurrentRecord();
		$container = $grid->getForm()->getComponent($this->name);

		$identifier = $grid->getModel()->getUniqueId($record);
		$container['ids'][$index]->setValue($identifier);

		return $this->controls[$index];
	}



	/**
	 * @return array
	 */
	public function getChecked()
	{
		$keys = array_keys(array_filter($this->getValues()));
		return $this->getGrid()->getModel()->getItemsByUniqueIds($keys);
	}



	/**
	 * @return array
	 */
	public function getValues()
	{
		$container = $this->getGrid()->getForm()->getComponent($this->name);

		$values = array();
		foreach ($this->controls as $index => $control) {
			$id = $container['ids'][$index]->value;

			if ($id) {
				$values[$id] = $control->value;
			}
		}

		return $values;
	}



	/**
	 * Render cell
	 */
	public function renderCell()
	{
		echo call_user_func(array($this->renderer, 'renderCell'), $this);
	}

}