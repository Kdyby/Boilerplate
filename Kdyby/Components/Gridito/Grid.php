<?php

namespace Gridito;

use Nette\ComponentContainer, Nette\Environment, Nette\Paginator;

/**
 * Grid
 *
 * @author Jan Marek
 * @license MIT
 */
class Grid extends \Nette\Application\Control
{
	// <editor-fold defaultstate="collapsed" desc="variables">

	/** @var IModel */
	private $model;

	/** @var Paginator */
	private $paginator;

	/** @var int */
	private $defaultItemsPerPage = 20;

	/**
	 * @var int
	 * @persistent
	 */
	public $page = 1;

	/**
	 * @var string
	 * @persistent
	 */
	public $sortColumn = null;

	/**
	 * @var string
	 * @persistent
	 */
	public $sortType = null;

	/** @var string */
	private $ajaxClass = "ajax";

	/** @var bool */
	private $highlightOrderedColumn = true;

	/** @var string|callable */
	private $rowClass = null;

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="constructor">

	public function __construct(\Nette\IComponentContainer $parent = null, $name = null)
	{
		parent::__construct($parent, $name);

		$this->addComponent(new ComponentContainer, "toolbar");
		$this->addComponent(new ComponentContainer, "actions");
		$this->addComponent(new ComponentContainer, "columns");

		$this->paginator = new Paginator;
		$this->paginator->setItemsPerPage($this->defaultItemsPerPage);
	}

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="getters & setters">

	/**
	 * @param bool highlight ordered column
	 * @return Grid
	 */
	public function setHighlightOrderedColumn($highlightOrderedColumn)
	{
		$this->highlightOrderedColumn = (bool) $highlightOrderedColumn;
		return $this;
	}



	/**
	 * @return bool
	 */
	public function getHighlightOrderedColumn()
	{
		return $this->highlightOrderedColumn;
	}



	public function setRowClass($class)
	{
	    $this->rowClass = $class;
		return $this;
	}



	public function getRowClass($iterator, $row)
	{
		if (is_callable($this->rowClass)) {
			return call_user_func($this->rowClass, $iterator, $row);
		} elseif (is_string($this->rowClass)) {
			return $this->rowClass;
		} else {
			return null;
		}
	}



	/**
	 * Get model
	 * @return IModel
	 */
	public function getModel()
	{
		return $this->model;
	}



	/**
	 * Set model
	 * @param IModel model
	 * @return Grid
	 */
	public function setModel(IModel $model)
	{
		$this->getPaginator()->setItemCount($model->count());
		$this->model = $model;
		return $this;
	}



	/**
	 * Get items per page
	 * @return int
	 */
	public function getItemsPerPage()
	{
		return $this->getPaginator()->getItemsPerPage();
		return $this;
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
	 * Get ajax class
	 * @return string
	 */
	public function getAjaxClass()
	{
		return $this->ajaxClass;
	}



	/**
	 * Set ajax class
	 * @param string ajax class
	 * @return Grid
	 */
	public function setAjaxClass($ajaxClass)
	{
		$this->ajaxClass = $ajaxClass;
		return $this;
	}



	/**
	 * Get paginator
	 * @return Nette\Paginator
	 */
	public function getPaginator()
	{
		return $this->paginator;
	}



	/**
	 * Get security token
	 * @return string
	 */
	public function getSecurityToken()
	{
		$session = Environment::getSession(__CLASS__ . "-" . __METHOD__);

		if (empty($session->securityToken)) {
			$session->securityToken = md5(uniqid(mt_rand(), true));
		}

		return $session->securityToken;
	}



	/**
	 * Has toolbar
	 * @return bool
	 */
	public function hasToolbar()
	{
		return count($this["toolbar"]->getComponents()) > 0;
	}



	/**
	 * Has actions
	 * @return bool
	 */
	public function hasActions()
	{
		return count($this["actions"]->getComponents()) > 0;
	}

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="signals">

	/**
	 * Handle change page signal
	 * @param int page
	 */
	public function handleChangePage($page)
	{
		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}



	public function handleSort($sortColumn, $sortType)
	{
		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="rendering">

	/**
	 * Create template
	 * @return Template
	 */
	protected function createTemplate()
	{
		return parent::createTemplate()->setFile(__DIR__ . "/templates/grid.phtml");
	}



	/**
	 * Render grid
	 */
	public function render()
	{
		$this->paginator->setPage($this->page);
		$this->model->setLimit($this->paginator->getLength());
		$this->model->setOffset($this->paginator->getOffset());

		if ($this->sortColumn && $this["columns"]->getComponent($this->sortColumn)->isSortable()) {
			$this->model->setSorting($this->sortColumn, $this->sortType);
		}

		$this->template->render();
	}

	// </editor-fold>


	/**
	 * Add column
	 * @param string name
	 * @param string label
	 * @param array options
	 * @return Column
	 */
	public function addColumn($name, $label = null, array $options = array())
	{
		$column = new Column($this["columns"], $name);
		$column->setLabel($label);
		$this->setOptions($column, $options);
		return $column;
	}



	/**
	 * Add action button
	 * @param string button name
	 * @param string label
	 * @param array options
	 * @return Button
	 */
	public function addButton($name, $label = null, array $options = array())
	{
		$button = new Button($this["actions"], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Add window button
	 * @param string button name
	 * @param string label
	 * @param array options
	 * @return WindowButton
	 */
	public function addWindowButton($name, $label = null, array $options = array())
	{
		$button = new WindowButton($this["actions"], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Add action button to toolbar
	 * @param string button name
	 * @param string label
	 * @param array options
	 * @return Button
	 */
	public function addToolbarButton($name, $label = null, array $options = array())
	{
		$button = new Button($this["toolbar"], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Add window button to toolbar
	 * @param string button name
	 * @param string label
	 * @param array options
	 * @return WindowButton
	 */
	public function addToolbarWindowButton($name, $label = null, array $options = array())
	{
		$button = new WindowButton($this["toolbar"], $name);
		$button->setLabel($label);
		$this->setOptions($button, $options);
		return $button;
	}



	/**
	 * Set page
	 * @param int page
	 */
	private function setPage($page)
	{
		$this->getPaginator()->setPage($page);
	}



	protected function setOptions($object, $options)
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

}