<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder;

use Kdyby;
use Nette\Forms\Container;
use Nette;
use Nette\ComponentModel\IContainer;
use Nette\Application\UI\PresenterComponent;



/**
 * @author Filip Procházka
 *
 * @property-read Filters\FiltersMap $filtersMap
 */
class GridFilters extends PresenterComponent
{

	/** @var Models\IModel */
	private $model;

	/** @var Filters\FiltersMap */
	private $filtersMap;

	/** @var Container */
	private $formContainer;



	/**
	 * @param Models\IModel $model
	 */
	public function __construct(Models\IModel $model)
	{
		$this->model = $model;
		$this->filtersMap = new Filters\FiltersMap($model->createFragmentsBuilder());
	}



	/**
	 * @param IContainer $parent
	 * @throws Nette\InvalidStateException
	 */
	protected function validateParent(IContainer $parent)
	{
		parent::validateParent($parent);

		if (!$parent instanceof Grid) {
			$grid = $parent->lookup('Kdyby\\Components\\Grinder\\Grid', FALSE);

			if (!$grid instanceof Grid) {
				throw new Nette\InvalidStateException("Parent or one of ancesors must be instance of Kdyby\\Components\\Grinder\\Grid.");
			}
		}
	}



	/**
	 * @return Filters\FiltersMap
	 */
	public function getFiltersMap()
	{
		return $this->filtersMap;
	}



	/**
	 * @param Form $form
	 * @return GridFilters
	 */
	public function setFormContainer(Container $container)
	{
		$this->formContainer = $container;
		return $this;
	}



	/**
	 * @return Container
	 */
	public function getFormContainer()
	{
		return $this->formContainer;
	}



	/**
	 * @return Filters\Form
	 */
	public function getForm()
	{
		return $this->formContainer->lookup('Nette\Forms\Form');
	}



	/**
	 * @return Grid
	 */
	public function getGrid()
	{
		return $this->lookup('Kdyby\\Components\\Grinder\\Grid');
	}



	/**
	 * @return GridFilters
	 */
	public function createButtons()
	{
		$this->getForm()->addSubmit('filter', "Filtrovat");
		$this->getForm()->addSubmit('clear', "Zrušit filtr")
			->setValidationScope(FALSE)
			->onClick[] = array($this->getForm(), 'ResetFilters');

		return $this;
	}



	/**
	 * @param string $column
	 * @param PresenterComponent $control
	 * @param string $paramName
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addParameter($column, PresenterComponent $control, $paramName, $method = '=', $name = NULL)
	{
		return $this->filtersMap->create($name, $column, function () use ($control, $paramName) {
			return $control->getParam($paramName);
		}, $method);
	}



	/**
	 * @param string $column
	 * @param mixed $value
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addValue($column, $value, $method = '=', $name = NULL)
	{
		return $this->filtersMap->create($name, $column, function () use ($value) {
			return $value;
		}, $method);
	}



	/**
	 * @param string $column
	 * @param Nette\Forms\IControl $field
	 * @param string|\Closure|Nette\Callback $method
	 * @return Filters\Filter
	 */
	public function addField($column, Nette\Forms\IControl $field, $method = '=')
	{
		$filter = $this->filtersMap->create($field->name, $column, NULL, $method);
		$filter->control = $field;

		$grid = $this->getGrid();
		$filter->source = function () use ($grid, $filter) {
			return isset($grid->filter[$filter->name]) ? $grid->filter[$filter->name] : NULL;
		};

		return $filter;
	}



	/**
	 * @param string $column
	 * @param string $label
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addTextField($column, $label, $method = '=', $name = NULL)
	{
		$filter = $this->filtersMap->create($name, $column, NULL, $method);
		$filter->control = $this->getFormContainer()->addText($filter->name, $label);

		$grid = $this->getGrid();
		$filter->source = function () use ($grid, $filter) {
			return isset($grid->filter[$filter->name]) ? $grid->filter[$filter->name] : NULL;
		};

		return $filter;
	}



	/**
	 * @param string $column
	 * @param string $label
	 * @param array $items
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addSelectField($column, $label, array $items = array(), $method = '=', $name = NULL)
	{
		$filter = $this->filtersMap->create($name, $column, NULL, $method);
		$filter->control = $this->getFormContainer()->addSelect($filter->name, $label, $items);

		$grid = $this->getGrid();
		$filter->source = function () use ($grid, $filter) {
			return isset($grid->filter[$filter->name]) ? $grid->filter[$filter->name] : NULL;
		};

		return $filter;
	}



	/**
	 * @param string $column
	 * @param array $items
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addCheckboxListField($column, array $items = array(), $method = '=', $name = NULL)
	{
		$filter = $this->filtersMap->create($name, $column, NULL, $method);

		$list = $filter->control = $this->getFormContainer()->addContainer($filter->name);
		foreach ($items as $id => $name) {
			$list->addCheckbox($id, $name);
		}

		$grid = $this->getGrid();
		$filter->source = function () use ($grid, $filter) {
			$value = isset($grid->filter[$filter->name]) ? $grid->filter[$filter->name] : NULL;
			return $value ? array_keys(array_filter($value)) : NULL;
		};

		return $filter;
	}



	/**
	 * @param string $column
	 * @param string $label
	 * @param array $items
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addDateField($column, $label, $method = '=', $name = NULL)
	{
		$filter = $this->filtersMap->create($name, $column, NULL, $method);
		$filter->control = $this->getFormContainer()->addDate($filter->name, $label);

		$grid = $this->getGrid();
		$filter->source = function () use ($grid, $filter) {
			$value = isset($grid->filter[$filter->name]) ? $grid->filter[$filter->name] : NULL;
			return $value ? Nette\DateTime::from($value) : NULL;
		};

		return $filter;
	}



	/**
	 * @param string $column
	 * @param string $label
	 * @param string|\Closure|Nette\Callback $method
	 * @param string|NULL $name
	 * @return Filters\Filter
	 */
	public function addDateTimeField($column, $label, $method = '=', $name = NULL)
	{
		$filter = $this->filtersMap->create($name, $column, NULL, $method);
		$filter->control = $this->getFormContainer()->addDateTime($filter->name, $label);

		$grid = $this->getGrid();
		$filter->source = function () use ($grid, $filter) {
			$value = isset($grid->filter[$filter->name]) ? $grid->filter[$filter->name] : NULL;
			return $value ? Nette\DateTime::from($value) : NULL;
		};

		return $filter;
	}

}