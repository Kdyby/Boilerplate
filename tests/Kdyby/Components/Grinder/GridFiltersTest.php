<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Components\Grinder;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette;



require_once KDYBY_FRAMEWORK_DIR . '/Application/UI/Form.php';

/**
 * @author Filip Procházka
 */
class GridFiltersTest extends Kdyby\Testing\Test
{

	/** @var Grinder\Grid */
	private $grid;

	/** @var Grinder\IModel */
	private $model;

	/** @var Grinder\GridFilters */
	private $filters;



	public function setUp()
	{
		$this->model = $this->getMock('Kdyby\Components\Grinder\IModel');
		$this->model->expects($this->any())
			->method('createFragmentsBuilder')
			->will($this->returnValue($this->getMock('Kdyby\Components\Grinder\Filters\IFragmentsBuilder')));

		$this->grid = new Grinder\Grid($this->model);
		$this->filters = $this->grid->getFilters();
	}



	public function testFiltersMap()
	{
		$this->assertInstanceOf('Kdyby\Components\Grinder\Filters\FiltersMap', $this->filters->getFiltersMap());
		$this->assertSame($this->filters->getFiltersMap(), $this->filters->getFiltersMap());
	}



	public function testFiltersHasNoFormContainerOnCreated()
	{
		$filters = new Grinder\GridFilters($this->model);
		$this->assertNull($filters->getFormContainer());
	}



	/**
	 * @expectedException Nette\InvalidStateException
	 */
	public function testFiltersCantReturnFormWhenHasNoFormContainerException()
	{
		$filters = new Grinder\GridFilters($this->model);
		$filters->getForm();
	}



	public function testFormContainer()
	{
		$filters = new Grinder\GridFilters($this->model);

		$form = new Nette\Application\UI\Form();
		$container = $form->addContainer('container');

		$filters->setFormContainer($container);
		$this->assertSame($container, $filters->getFormContainer());
		$this->assertSame($form, $filters->getForm());
	}



	public function testCreateButtons()
	{
		$this->filters->createButtons();

		$filterButton = $this->filters->getForm()->getComponent('filter');
		$this->assertInstanceOf('Nette\Forms\Controls\SubmitButton', $filterButton);

		$clearButton = $this->filters->getForm()->getComponent('clear');
		$this->assertInstanceOf('Nette\Forms\Controls\SubmitButton', $clearButton);

		$this->assertFalse($clearButton->getValidationScope());
		$this->assertEventHasCallback(callback($this->filters->getForm(), 'ResetFilters'), $clearButton, 'onClick');
	}



	public function testAddParameterFilter()
	{
		$control = new GenericComponentMock();
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$filter = $this->filters->addParameter('column', $control, 'param', 'equals', 'name');
		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($control, $filter->getControl());
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}



	public function testAddValueFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$filter = $this->filters->addValue('column', 10, 'equals', 'name');
		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}



	public function testAddFieldFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();
		$field = new Nette\Forms\Controls\TextInput();
		$field->setParent(NULL, 'name');

		$filter = $this->filters->addField('column', $field, 'equals');
		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($field, $filter->getControl());
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}



	public function testAddTextFieldFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$filter = $this->filters->addTextField('column', 'label', 'equals', 'name');
		$control = $this->filters->getFormContainer()->getComponent('name');

		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($control, $filter->getControl());
		$this->assertInstanceOf('Nette\Forms\Controls\TextInput', $control);
		$this->assertSame('label', $control->caption);
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}



	public function testAddSelectFieldFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$items = array('key' => 'Item');
		$filter = $this->filters->addSelectField('column', 'label', $items, 'equals', 'name');
		$control = $this->filters->getFormContainer()->getComponent('name');

		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($control, $filter->getControl());
		$this->assertInstanceOf('Nette\Forms\Controls\SelectBox', $control);
		$this->assertSame('label', $control->caption);
		$this->assertSame($items, $control->getItems());
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}



	public function testAddCheckboxListFieldFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$items = array('one' => 'Item1', 'two' => 'Item2', 'three' => 'Item3');
		$filter = $this->filters->addCheckboxListField('column', $items, 'equals', 'name');
		$container = $this->filters->getFormContainer()->getComponent('name');

		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($container, $filter->getControl());
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());

		foreach ($items as $name => $label) {
			$control = $container->getComponent($name, FALSE);
			$this->assertInstanceOf('Nette\Forms\Controls\Checkbox', $control);
			$this->assertSame($label, $control->caption);
		}
	}



	public function testAddDateFieldFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$filter = $this->filters->addDateField('column', 'label', 'equals', 'name');
		$control = $this->filters->getFormContainer()->getComponent('name');

		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($control, $filter->getControl());
		$this->assertInstanceOf('Kdyby\Forms\Controls\DateTime', $control);
		$this->assertSame('label', $control->caption);
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}



	public function testAddDateTimeFieldFilter()
	{
		$fragmentBuilder = $this->filters->getFiltersMap()->getFragmentsBuilder();

		$filter = $this->filters->addDateField('column', 'label', 'equals', 'name');
		$control = $this->filters->getFormContainer()->getComponent('name');

		$this->assertSame('column', $filter->getColumn());
		$this->assertSame('name', $filter->getName());
		$this->assertSame($control, $filter->getControl());
		$this->assertInstanceOf('Kdyby\Forms\Controls\DateTime', $control);
		$this->assertSame('label', $control->caption);
		$this->assertEquals(callback($fragmentBuilder, 'buildEquals'), $filter->getMethod());
		$this->assertInstanceOf('Closure', $filter->getSource()->getNative());
	}

}