<?php

namespace Kdyby\Components\Grinder;

use Nette;
use Nette\ComponentContainer;
use Nette\Environment;
use Nette\Paginator;
use Kdyby;
use Kdyby\Components\VisualPaginator\VisualPaginator;
use Kdyby\Components\Grinder\Renderers\IGridRenderer;



/**
 * Grid
 *
 * @todo filtry
 * @todo nested nodes manipulation (column + renderer)
 * @todo column s indexem záznamu
 * @todo inline editation
 * @todo ajax per each item
 * @todo vícenásobné řazení
 *
 * @author Jan Marek
 * @author Filip Procházka
 * @license MIT
 */
class Grid extends Nette\Application\Control
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

	/** @var int */
	public $defaultItemsPerPage = 20;

	/** @var Models\IModel */
	private $model;

	/** @var int */
	private $index;

	/** @var mixed */
	private $data;

	/** @var Nette\Web\SessionNamespace */
	private $session;

	/** @var IGridRenderer */
	private $renderer;

	/** @var string|callable */
	private $rowHtmlClass;

	/** @var string */
	private $paginatorPlacemenet = self::PLACEMENT_BOTTOM;

	/** @var string */
	private $toolbarPlacemenet = self::PLACEMENT_TOP;



	/**
	 * @param Models\IModel $model
	 * @param Nette\Web\Session $session
	 */
	public function __construct(Models\IModel $model)
	{
		parent::__construct(NULL, NULL);

		$this['actions'] = new ComponentContainer;
		$this['toolbar'] = new ComponentContainer;
		$this['columns'] = new ComponentContainer;
		$this['form'] = $form = new GridForm;

		// model
		$this->model = $model;

		// paginator
		$this->getPaginator()->setItemsPerPage($this->defaultItemsPerPage);
	}



	/**
	 * @return Kdyby\Components\Grinder\GridForm
	 */
	public function getForm()
	{
		return $this->getComponent('form');
	}


	/********************* Data *********************/


	/**
	 * @return Kdyby\Components\Grinder\Models\IModel
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
	 * @param Nette\Web\Session $session
	 */
	public function setUpProtection(Nette\Web\Session $session)
	{
		$this->session = $session->getNamespace(__CLASS__);
	}



	/**
	 * CSRF protection
	 * @return string	security token
	 */
	public function getSecurityToken()
	{
		if (!$this->session->securityToken) {
			$this->session->securityToken = md5(uniqid(mt_rand(), true));
		}

		return $this->session->securityToken;
	}


	/********************* Actions *********************/


	/**
	 * Has actions
	 * @return bool
	 */
	public function hasActions()
	{
		return count($this->getActions()) > 0;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getActions()
	{
		return $this["actions"]->getComponents();
	}



	/**
	 * Add action button
	 * @param string action name
	 * @param string caption
	 * @param array options
	 * @return Action
	 */
	public function addAction($name, $caption = NULL, array $options = array())
	{
		throw new \NotImplementedException;

		$action = new Action($this["actions"], $name);
		$action->setCaption($caption);
		$this->setOptions($action, $options);
		return $action;
	}


	/********************* Toolbar *********************/


	/**
	 * @param string $toolbarPlacemenet
	 */
	public function setToolbarPlacement($toolbarPlacemenet)
	{
		$this->toolbarPlacemenet = $toolbarPlacemenet;
	}



	/**
	 * @return bool
	 */
	public function hasTopToolbar()
	{
		return $this->toolbarPlacemenet === self::PLACEMENT_BOTH || $this->toolbarPlacemenet === self::PLACEMENT_TOP;
	}



	/**
	 * @return bool
	 */
	public function hasBottomToolbar()
	{
		return $this->toolbarPlacemenet === self::PLACEMENT_BOTH || $this->toolbarPlacemenet === self::PLACEMENT_BOTTOM;
	}



	/**
	 * Has toolbar
	 * @return bool
	 */
	public function hasToolbar()
	{
		return count($this->getToolbar()) > 0;
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getToolbar()
	{
		return $this->getComponent('toolbar')->getComponents();
	}



	/**
	 * Add action button to toolbar
	 * @param string button name
	 * @param string caption
	 * @param array options
	 * @return Kdyby\Components\Grinder\Toolbar\ButtonAction
	 */
	public function addToolbarAction($name, $caption = NULL, array $options = array())
	{
		$this->getComponent('toolbar')->addComponent($action = new Toolbar\ButtonAction($caption), $name);
		$this->setOptions($action, $options);

		return $action;
	}


	/********************* Columns *********************/


	/**
	 * Add column
	 * @param string name
	 * @param string caption
	 * @param array options
	 * @return Column
	 */
	public function addColumn($name, $caption = NULL, array $options = array())
	{
		$this['columns']->addComponent($column = new Columns\Column, $name);

		$this->setOptions($column, $options);
		$column->setCaption($caption);

		return $column;
	}



	/**
	 * Add column
	 * @param string name
	 * @param string caption
	 * @param array options
	 * @return Column
	 */
	public function addCheckColumn($name, $caption = NULL, array $options = array())
	{
		$this['columns']->addComponent($column = new Columns\CheckColumn, $name);

		$this->setOptions($column, $options);
		$column->setCaption($caption);

		return $column;
	}



	/**
	 * Add column
	 * @param string name
	 * @param string caption
	 * @param array options
	 * @return Column
	 */
	public function addFormColumn($name, $control = NULL, $caption = NULL, array $options = array())
	{
		throw new \NotImplementedException;

		$control = $control ?: new Nette\Forms\Checkbox;
		$this['columns']->addComponent($column = new Columns\FormColumn($control), $name);

		$this->setOptions($column, $options);
		$column->setCaption($caption);

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
	 * @return Kdyby\Components\Grinder\Columns\BaseColumn
	 */
	public function getColumn($name)
	{
		return $this->getComponent('columns')->getComponent($name);
	}


	/********************* Filters *********************/


	/**
	 * @param string $name
	 */
	protected function createComponentFilters($name)
	{
		throw new \NotImplementedException();
	}



	/**
	 *
	 */
	public function getFilters()
	{
		return $this['filters'];
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


	/********************* Helpers *********************/


	/**
	 * @param object $object
	 * @param array $options
	 */
	protected function setOptions($object, array $options)
	{
		foreach	($options as $option => $value) {
			$method = "set" . ucfirst($option);
			if (method_exists($object, $method)) {
				$object->$method($value);
			} else {
				throw new \InvalidArgumentException("Option with name $option does not exist.");
			}
		}
	}


	/********************* Paging *********************/


	/**
	 * Get paginator
	 * @return Nette\Paginator
	 */
	public function getPaginator()
	{
		return $this['vp']->paginator;
	}



	/**
	 * Set page
	 * @param int page
	 */
	private function setPage($page)
	{
		$this->getPaginator()->setPage($page);
	}



	/**
	 * Get items per page
	 * @return int
	 */
	public function getItemsPerPage()
	{
		return $this->getPaginator()->getItemsPerPage();
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



	/**
	 * @param string $name
	 * @return VisualPaginator
	 */
	protected function createComponentVp($name)
	{
		return new VisualPaginator($this, $name);
	}



	/**
	 * @param string $placement
	 */
	public function setPaginatorPlacemenet($placement)
	{
		$this->paginatorPlacemenet = $placement;
	}



	/**
	 * @return bool
	 */
	public function hasTopPaginator()
	{
		return $this->paginatorPlacemenet === self::PLACEMENT_BOTH || $this->paginatorPlacemenet === self::PLACEMENT_TOP;
	}



	/**
	 * @return bool
	 */
	public function hasBottomPaginator()
	{
		return $this->paginatorPlacemenet === self::PLACEMENT_BOTH || $this->paginatorPlacemenet === self::PLACEMENT_BOTTOM;
	}


	/********************* Rendering *********************/



	/**
	 * @param Kdyby\Components\Grinder\Renderers\IGridRenderer $renderer
	 */
	public function setRenderer(IGridRenderer $renderer)
	{
		$this->renderer = $renderer;
	}



	/**
	 * @return Kdyby\Components\Grinder\Renderers\IGridRenderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}



	/**
	 * Renders grid
	 */
	public function render()
	{
		$this->getPaginator()->setItemCount($this->getModel()->count());

		$this->getModel()->setLimit($this->getPaginator()->getLength());
		$this->getModel()->setOffset($this->getPaginator()->getOffset());

		if ($this->sortColumn && $this["columns"]->getComponent($this->sortColumn)->isSortable()) {
			$this->getModel()->setSorting($this->sortColumn, $this->sortType);
		}

		foreach ($this->getColumns() as $column) {
			$column->setRenderer($this->getRenderer());
		}

		$this->getRenderer()->render($this);
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