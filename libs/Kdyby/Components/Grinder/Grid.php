<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\ComponentModel\Container;
use Nette\Environment;
use Nette\Utils\Paginator;
use Kdyby;
use Kdyby\Components\VisualPaginator\VisualPaginator;



/**
 * Grid
 *
 * @todo storage na filtry a page
 * @todo saveState & loadState (filtry, page, ..)
 * @todo inline editation
 * @todo nested nodes manipulation (column + renderer)
 * @todo column s indexem záznamu
 * @todo ajax per each item
 * @todo vícenásobné řazení
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
class Grid extends Nette\Application\UI\Control
{
	const PLACEMENT_TOP = 'top';
	const PLACEMENT_BOTTOM = 'bottom';
	const PLACEMENT_BOTH = 'both';

	/** @persistent int */
	public $page = 1;

	/** @persistent string */
	public $sortColumn = NULL;

	/** @persistent string */
	public $sortType = NULL;

	/** @persistent array */
	public $filter = array();

	/** @var int */
	public $defaultItemsPerPage = 20;

	/** @var Models\IModel */
	private $model;

	/** @var boolean */
	private $isModelSetted = FALSE;

	/** @var int */
	private $index;

	/** @var mixed */
	private $data;

	/** @var Nette\Http\SessionNamespace */
	private $session;

	/** @var IGridRenderer */
	private $renderer = FALSE;

	/** @var string|Nette\Utils\Html */
	private $emptyResultMessage;

	/** @var string|callable */
	private $rowHtmlClass;

	/** @var Kdyby\DI\Container */
	private $context;



	/**
	 * @param Models\IModel $model
	 * @param Nette\Http\Session $session
	 */
	public function __construct(Models\IModel $model)
	{
		parent::__construct(NULL, NULL);
		$this->monitor('Nette\Application\UI\Presenter');

		$this->addComponent(new Container, 'columns');
		$this->addComponent(new Actions\ActionsContainer, 'actions');
		$this->addComponent(new Actions\ToolbarActionsContainer, 'toolbar');
		$this->addComponent(new Forms\GridForm, 'form');
		$this->addComponent(new GridPaginator, 'paginator');

		// model
		$this->model = $model;

		// paginator
		$this->getPaginator()->setItemsPerPage($this->defaultItemsPerPage);
	}



	/**
	 * @param Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Presenter) {
			return;
		}

		// steal the context from presenter :)
		$this->context = $obj->getContext();

		// fill the defaults into filters
		if ($this->filter) {
			foreach ($this->getFilters()->getFiltersMap() as $filter) {
				if (!$filter->getControl() instanceof Nette\Forms\IControl) {
					continue;
				}

				$filter->getControl()->setDefaultValue($filter->getValue());
			}
		}
	}



	/**
	 * @return Kdyby\DI\Container
	 */
	public function getContext()
	{
		if (!$this->context) {
			throw new Nette\InvalidStateException("Grid was not yet attached to presenter, therefore you cannot access to context.");
		}

		return $this->context;
	}



	/**
	 * @param IComponent $component
	 * @throws Nette\NotSupportedException
	 */
	final public function removeComponent(IComponent $component)
	{
		throw new Nette\NotSupportedException;
	}



	/**
	 * @return GridForm
	 */
	public function getForm()
	{
		return $this->getComponent('form');
	}


	/********************* Data *********************/


	/**
	 * @return Models\IModel
	 */
	public function getModel()
	{
		return $this->model;
	}



	/**
	 * @return GridIterator
	 */
	public function getIterator()
	{
		return new GridIterator($this, $this->model);
	}



	/**
	 * @param int $key
	 * @param array|object $record
	 */
	public function bindRecord($index, $record)
	{
		$this->index = $index;
		$this->data = $record;
	}



	/**
	 * @return mixed
	 */
	public function getCurrentIndex()
	{
		return $this->index;
	}



	/**
	 * @return mixed
	 */
	public function getCurrentRecord()
	{
		return $this->data;
	}


	/********************* Security *********************/


	/**
	 * CSRF protection
	 * @param Nette\Http\Session $session
	 */
	public function setUpProtection(Nette\Http\Session $session)
	{
		$this->session = $session->getSection(__CLASS__);

		if (!$this->session->securityToken) {
			$this->session->securityToken = Nette\Utils\Strings::random(6);
		}
	}



	/**
	 * CSRF protection
	 * @return string	security token
	 */
	public function getSecurityToken()
	{
		return $this->session->securityToken;
	}


	/********************* Actions *********************/


	/**
	 * @return Actions\ActionsContainer
	 */
	public function getActionsContainer()
	{
		return $this->getComponent('actions');
	}



	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @param string $insertBefore
	 * @return Actions\LinkAction
	 */
	public function addAction($name, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$action = $this->add($link = new Actions\LinkAction, $name, $insertBefore);
		$this->setOptions($action, array('caption' => $caption) + $options);

		return $action;
	}



	/**
	 * @param string $name
	 * @return Actions\BaseAction
	 */
	public function getAction($name)
	{
		return $this->getActionsContainer()->getComponent($name);
	}


	/********************* Toolbar *********************/


	/**
	 * @return Actions\ToolbarActionsContainer
	 */
	public function getToolbar()
	{
		return $this->getComponent('toolbar');
	}



	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @param string|int $insertBefore
	 * @return Actions\ButtonAction
	 */
	public function addToolbarAction($name, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$this->getToolbar()->addComponent($action = new Actions\ButtonAction($caption), $name, $insertBefore);
		$this->setOptions($action, array('caption' => $caption) + $options);

		return $action;
	}


	/********************* Columns *********************/


	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @param string|int $insertBefore
	 * @return Columns\Column
	 */
	public function addColumn($name, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$column = $this->add(new Columns\Column, $name, $insertBefore);
		$this->setOptions($column, array('caption' => $caption) + $options);

		return $column;
	}



	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @param string|int $insertBefore
	 * @return Columns\CheckColumn
	 */
	public function addCheckColumn($name, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$column = $this->add(new Columns\CheckColumn, $name, $insertBefore);
		$this->setOptions($column, array('caption' => $caption) + $options);

		return $column;
	}



	/**
	 * @param string $name
	 * @param string|callback|Html $image
	 * @param string $caption
	 * @param array $options
	 * @param string|int $insertBefore
	 * @return Columns\ImageColumn
	 */
	public function addImageColumn($name, $image, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$column = $this->add(new Columns\ImageColumn($image), $name, $insertBefore);
		$this->setOptions($column, array('caption' => $caption) + $options);

		return $column;
	}



	/**
	 * @param string $name
	 * @param Nette\Forms\IControl $control
	 * @param string $caption
	 * @param array $options
	 * @param string|int $insertBefore
	 * @return Columns\FormColumn
	 */
	public function addFormColumn($name, Nette\Forms\IControl $control, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$column = $this->add(new Columns\FormColumn($control), $name, $insertBefore);
		$this->setOptions($column, array('caption' => $caption) + $options);

		return $column;
	}



	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @param string|int $insertBefore
	 * @return Columns\ActionsColumn
	 */
	public function addActionsColumn($name, $caption = NULL, array $options = array(), $insertBefore = NULL)
	{
		$column = $this->add(new Columns\ActionsColumn, $name, $insertBefore);
		$action = $column->addAction(new Actions\LinkAction, $name);

		$this->setOptions($column, array('caption' => $caption) + $options, FALSE);
		$this->setOptions($action, $options + array('caption' => $caption), FALSE);

		return $column;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getColumns()
	{
		return $this->getComponent('columns')->getComponents();
	}



	/**
	 * @param string $name
	 * @return Columns\BaseColumn
	 */
	public function getColumn($name)
	{
		return $this->getComponent('columns')->getComponent($name);
	}


	/********************* Filters *********************/


	/**
	 * @return GridFilters
	 */
	protected function createComponentFilters()
	{
		$filters = new GridFilters($this->model);

		$this->addComponent($form = new Filters\Form($filters->getFiltersMap()), 'filtersForm');
		$filters->setFormContainer($form->getComponent('filters'));

		return $filters;
	}



	/**
	 * @return GridFilters
	 */
	public function getFilters()
	{
		return $this->getComponent('filters');
	}



	/**
	 * @return boolean
	 */
	public function hasFilters()
	{
		return (bool)$this->getFilters()->getFiltersMap()->getIterator()->count();
	}


	/********************* Handlers *********************/


	/**
	 * @param string $sortColumn
	 * @param string $sortType
	 */
	public function handleSort($sortColumn, $sortType)
	{
		if ($this->getPresenter()->isAjax()) {
			$this->invalidateControl();
		}
	}



	/**
	 * @param string $action
	 * @param string $token
	 * @param string $id
	 */
	public function handleAction($action, $token, $id = NULL)
	{
		if ($token !== $this->getSecurityToken()) {
			throw new Nette\Application\ForbiddenRequestException("Security token does not match. Possible CSRF attack.");
		}

		$action = $this->getAction($action);

		if ($action instanceof Actions\LinkAction) {
			$action->handleClick($id);
		}

		if ($this->getPresenter()->isAjax()) {
			return $this->invalidateControl();
		}

		$this->getPresenter()->redirect("this");
	}


	/********************* Helpers *********************/


	/**
	 * @param GridComponent $component
	 * @param string $name
	 * @param string $insertBefore
	 * @return GridComponent
	 */
	public function add($component, $name = NULL, $insertBefore = NULL)
	{
		if (!$component instanceof GridComponent) {
			throw new \InvalidArgumentException("Given component must be instanceof Kdyby\\Components\\Grinder\\GridComponent");
		}

		$name = $name ?: $component->name;
		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Component must have name or name must be explicitly set by argument \$name.");
		}

		if ($component instanceof Actions\BaseAction) {
			$this->getComponent('actions')->addComponent($component, $name, $insertBefore);
			return $component;
		}

		if ($component instanceof Columns\BaseColumn) {
			$this->getComponent('columns')->addComponent($component, $name, $insertBefore);
			return $component;
		}

		throw new \InvalidArgumentException("Unknown type of component '" . get_class($component) . "', with name " . $name . ".");
	}



	/**
	 * @param object $object
	 * @param array $options
	 * @param boolean $exceptionOnInvalid
	 */
	protected function setOptions($object, array $options, $exceptionOnInvalid = TRUE)
	{
		if (!is_object($object)) {
			throw new \InvalidArgumentException("Can by applied only to objects.");
		}

		foreach	($options as $option => $value) {
			$option = ucfirst($option);

			if (method_exists($object, $method = "set" . $option)) {
				$object->$method($value);

			} elseif (method_exists($object, $method = "add" . $option)) {
				$object->$method($value);

			} elseif ($exceptionOnInvalid) {
				throw new \InvalidArgumentException("Option with name $option does not exist.");
			}
		}
	}



	/**
	 * @param string $paramName
	 * @param bool $need
	 * @return mixed|NULL
	 */
	public function getRecordProperty($paramName, $need = TRUE)
	{
		$record = $this->getCurrentRecord();

		if (isset($record->$paramName)) {
			return $record->$paramName;

		} elseif (method_exists($record, $method = 'get' . ucfirst($paramName))) {
			return $record->$method();

		} elseif (method_exists($record, $method = 'is' . ucfirst($paramName))) {
			return $record->$method();

		} elseif ($need) {
			throw new Nette\InvalidStateException("Record " . (is_object($record) ? "of entity " . get_class($record) . ' ' : NULL) . "has no parameter named '" . $paramName . "'.");
		}

		return NULL;
	}



	/**
	 * @param string $name
	 * @param array $args
	 */
	public function __call($name, $args)
	{
		if (substr($name, 0, 6) !== 'render') {
			return parent::__call($name, $args);
		}

		return $this->render(lcfirst(substr($name, 6)), $args);
	}


	/********************* Paging *********************/


	/**
	 * Get paginator
	 * @return GridPaginator
	 */
	public function getVisualPaginator()
	{
		return $this->getComponent('paginator');
	}


	/**
	 * Get paginator
	 * @return Paginator
	 */
	public function getPaginator()
	{
		return $this->getVisualPaginator()->getPaginator();
	}



	/**
	 * Set items per page
	 * @param int items per page
	 * @return Grid
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		$this->getPaginator()->setItemsPerPage($itemsPerPage);
		return $this;
	}


	/********************* Rendering *********************/


	/**
	 * @param string|Nette\Utils\Html $message
	 * @return Grid
	 */
	public function setEmptyResultMessage($message)
	{
		if (!is_string($message) && !$message instanceof Nette\Utils\Html) {
			throw new \InvalidArgumentException("Given message must be either string or instance of Nette\\Web\\Html, '" . gettype($message) . "' given.");
		}

		$this->emptyResultMessage = $message;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getEmptyResultMessage()
	{
		return $this->emptyResultMessage ?: "No corresponding results were found.";
	}



	/**
	 * @param IGridRenderer $renderer
	 * @return Grid
	 */
	public function setRenderer(IGridRenderer $renderer)
	{
		$this->renderer = $renderer;
		return $this;
	}



	/**
	 * @return IGridRenderer
	 */
	public function getRenderer()
	{
		if ($this->renderer === FALSE) {
			$this->renderer = new Renderers\TableRenderer;
		}

		return $this->renderer;
	}



	/**
	 * Renders grid
	 */
	public function render($part = NULL, array $args = array())
	{
		$this->beforeRender();
		$this->getRenderer()->render($this, $part, $args);
	}



	/**
	 * @return void
	 */
	private function beforeRender()
	{
		if ($this->isModelSetted) {
			return;
		}

		// filter
		$this->getModel()->applyFilters($this->getFilters()->getFiltersMap());

		// count pages
		$this->getPaginator()->setItemCount($this->getModel()->count());

		// set limit & offset
		$this->getModel()->setLimit($this->getPaginator()->getLength());
		$this->getModel()->setOffset($this->getPaginator()->getOffset());

		// sorting
		if ($this->sortColumn && $this->getColumn($this->sortColumn)->isSortable()) {
			$this->getModel()->setSorting($this->sortColumn, $this->sortType);
		}

		// spread renderer
		foreach ($this->getColumns() as $column) {
			$column->setRenderer($this->getRenderer());
		}
		foreach ($this->getComponents(TRUE, 'Kdyby\Components\Grinder\Actions\BaseAction') as $action) {
			$action->setRenderer($this->getRenderer());
		}

		$this->isModelSetted = TRUE;
	}



	/**
	 * @param string|callable $class
	 * @return Grid
	 */
	public function setRowHtmlClass($class)
	{
	    $this->rowHtmlClass = $class;
		return $this;
	}



	/**
	 * @param \Iterator $iterator
	 * @return string
	 */
	public function getRowHtmlClass(\Iterator $iterator)
	{
		if (is_callable($this->rowHtmlClass)) {
			return call_user_func($this->rowHtmlClass, $iterator, $iterator->current());

		} elseif (is_string($this->rowHtmlClass)) {
			return $this->rowHtmlClass;
		}

		return NULL;
	}

}