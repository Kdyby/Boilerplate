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
use Nette\Utils\Html;
use Nette\Utils\Paginator;
use Kdyby;
use Kdyby\Components\VisualPaginator\VisualPaginator;



/**
 * @author Filip Procházka
 *
 * @todo storage na filtry a page
 * @todo saveState & loadState (filtry, page, ..)
 * @todo inline editation
 * @todo nested nodes manipulation (column + renderer)
 * @todo column s indexem záznamu
 * @todo ajax per each item
 * @todo vícenásobné řazení
 *
 * @property-read Nette\DI\Container $context
 * @property-read GridForm $form
 * @property-read Toolbar $toolbar
 * @property-read \ArrayIterator $columns
 * @property-read \ArrayIterator $actions
 * @property-read IModel $model
 * @property-read int $currentIndex
 * @property-read mixed $currentRecord
 * @property-read string $securityToken
 * @property-read GridFilters $filters
 * @property-read GridPaginator $visualPaginator
 * @property-read Paginator $paginator
 * @property-read int $itemsPerPage
 * @property-read string $rowHtmlClass
 * @property-read string $emptyResultMessage
 */
class Grid extends Nette\Application\UI\Control implements \IteratorAggregate
{

	const PLACEMENT_TOP = 'top';
	const PLACEMENT_BOTTOM = 'bottom';
	const PLACEMENT_BOTH = 'both';

	/** @persistent int */
	public $page = 1;

	/** @persistent string */
	public $sortColumn;

	/** @persistent string */
	public $sortType;

	/** @persistent array */
	public $filter = array();

	/** @var IModel */
	private $model;

	/** @var GridIterator */
	private $lastIterator;

	/** @var Nette\DI\Container */
	private $context;

	/** @var array */
	private $actions = array();

	/** @var array */
	private $componentNames = array();

	/** @var mixed */
	private $data;

	/** @var int */
	private $index;

	/** @var Nette\Http\SessionNamespace */
	private $session;

	/** @var IGridRenderer */
	private $renderer = FALSE;

	/** @var string|callback|Html */
	private $rowHtmlClass;

	/** @var string|Html */
	private $emptyResultMessage;



	/**
	 * @param IModel $model
	 * @param Nette\Http\Session $session
	 */
	public function __construct(IModel $model)
	{
		parent::__construct(NULL, NULL);

		$this->addComponent(new Container, 'columns');
		$this->addComponent(new Container, 'actions');
		$this->addComponent(new Toolbar, 'toolbar');
		$this->addComponent(new Forms\GridForm, 'form');
		$this->addComponent(new GridPaginator, 'paginator');

		// model
		$this->model = $model;
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
		// Doing this just for the components!! They need this!
		$this->context = $obj->getContext();

		// configure
		$this->configure($this->getPresenter());
		$this->configureToolbar($this->getToolbar());
		$this->configureFilters($this->getFilters(), $this->getPresenter());

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
	 * @return Nette\DI\Container
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
	 * @param IComponent $component
	 * @param string $name
	 * @param array $options
	 * @return IComponent
	 */
	public function add(IComponent $component, $name = NULL, $options = array())
	{
		$insertBefore = &$options['insertBefore'] ?: NULL;
		unset($options['insertBefore']);

		$name = $this->getComponentSafeName($component, $name);
		Kdyby\Tools\Objects::setProperties($component, $options);

		if ($component instanceof Columns\BaseColumn) {
			$this->getComponent('columns')->addComponent($component, $name, $insertBefore);
			return $component;
		}

		if ($component instanceof Actions\BaseAction) {
			if (isset($this->actions[$name])) {
				throw new Nette\InvalidArgumentException("Action named '" . $name . "' is already registered.");
			}

			if ($component->getGrid(FALSE) !== $this) {
				$this->getComponent('actions')->addComponent($component, $name, $insertBefore);
			}

			return $this->actions[$name] = $component;
		}

		throw new Nette\InvalidArgumentException("Unknown type of component '" . get_class($component) . "', with name " . $name . ".");
	}



	/**
	 * @internal
	 * @param IComponent $component
	 * @param string $realName
	 * @return string
	 */
	public function getComponentSafeName(IComponent $component, $realName)
	{
		$realName = $realName ?: $component->name;
		if (!is_string($realName) || $realName === '') {
			throw new Nette\InvalidArgumentException("Component must have name or name must be explicitly set by second argument \$name.");

		} elseif (!preg_match('#^[a-zA-Z0-9_.]+$#', $realName)) {
			throw new Nette\InvalidArgumentException("Component name must be non-empty alphanumeric string, '$realName' given.");
		}

		$oid = spl_object_hash($component);
		if (isset($this->componentNames[$oid])) {
			return $this->componentNames[$oid];
		}

		$safeName = str_replace('.', '', $realName);
		if ($f = array_search($safeName, $this->componentNames)) {
			throw new Nette\InvalidStateException("Name '" . $safeName . "' would colide with existing '" . $this->componentNames[$f] . "'.");
		}

		$this->componentNames[$oid] = $realName;
		return $safeName;
	}



	/**
	 * @internal
	 * @param IComponent $component
	 * @return string
	 */
	public function getComponentRealName(IComponent $component)
	{
		return $this->componentNames[spl_object_hash($component)];
	}



	/**
	 * @return GridForm
	 */
	public function getForm()
	{
		return $this->getComponent('form');
	}


	/********************* Construction *********************/


	/**
	 * Gets called on the right time for adding columns and actions
	 * @param Presenter $presenter
	 */
	protected function configure(Presenter $presenter)
	{
	}



	/**
	 * Gets called on the right time for adding toolbar actions
	 * @param Toolbar $toolbar
	 */
	protected function configureToolbar(Toolbar $toolbar)
	{
	}



	/**
	 * Gets called on the right time for setting filters
	 * @param Toolbar $toolbar
	 * @param Presenter $presenter
	 */
	protected function configureFilters(GridFilters $filters, Presenter $presenter)
	{
	}


	/********************* Actions *********************/


	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @return Actions\LinkAction
	 */
	public function addLink($name, $caption = NULL, array $options = array())
	{
		return $this->add(new Actions\LinkAction($caption), $name, $options);
	}



	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @return Actions\LinkAction
	 */
	public function addSubmit($name, $caption = NULL, array $options = array())
	{
		return $this->add(new Actions\ButtonAction($caption), $name, $options);
	}



	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @return Columns\ActionsColumn
	 */
	public function addActionsColumn($name, $caption = NULL, array $options = array())
	{
		return $this->add(new Columns\ActionsColumn($caption), $name, $options);
	}



	/**
	 * @param string $name
	 * @return Actions\BaseAction
	 */
	public function getAction($name)
	{
		return $this->actions[$name];
	}



	/**
	 * @return boolean
	 */
	public function hasActions()
	{
		return (bool)count($this->getComponent('actions')->getComponents());
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getActions()
	{
		return $this->getComponent('actions')->getComponents();
	}


	/********************* Toolbar *********************/


	/**
	 * @return Toolbar
	 */
	public function getToolbar()
	{
		return $this->getComponent('toolbar');
	}


	/********************* Columns *********************/


	/**
	 * @param string $name
	 * @param string $caption
	 * @param array $options
	 * @return Columns\Column
	 */
	public function addColumn($name, $caption = NULL, array $options = array())
	{
		return $this->add(new Columns\Column($caption), $name, $options);
	}



	/**
	 * @param string $name
	 * @param array $options
	 * @return Columns\CheckColumn
	 */
	public function addCheckColumn($name, array $options = array())
	{
		return $this->add(new Columns\CheckColumn, $name, $options);
	}



	/**
	 * @param string $name
	 * @param string|callback|Html $image
	 * @param array $options
	 * @return Columns\ImageColumn
	 */
	public function addImageColumn($name, $image, array $options = array())
	{
		return $this->add(
				new Columns\Column(NULL, new Components\Image),
				$name,
				array('image' => $image) + $options
			);
	}



	/**
	 * @param string $name
	 * @param string|callback|Html $icon
	 * @param array $options
	 * @return Columns\ImageColumn
	 */
	public function addIconColumn($name, $icon, array $options = array())
	{
		return $this->add(
				new Columns\Column(NULL, new Components\Icon),
				$name,
				array('image' => $image) + $options
			);
	}



	/**
	 * @param string $name
	 * @param Nette\Forms\IControl $control
	 * @param string $caption
	 * @param array $options
	 * @return Columns\FormColumn
	 */
	public function addFormColumn($name, Nette\Forms\IControl $control, array $options = array())
	{
		return $this->add(new Columns\FormColumn($control), $name, $options);
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
	 * @param boolean $need
	 * @return Columns\BaseColumn
	 */
	public function getColumn($name, $need = TRUE)
	{
		return $this->getComponent('columns')->getComponent($name, $need);
	}


	/********************* Data *********************/


	/**
	 * @return IModel
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
		return $this->lastIterator = new GridIterator($this, $this->model);
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



	/**
	 * @param string $paramName
	 * @return mixed|NULL
	 */
	public function getRecordProperty($paramName, $need = TRUE)
	{
		return Kdyby\Tools\Objects::expand($paramName, $this->getCurrentRecord(), $need);
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
		$this->invalidateControl();
		if (!$this->getPresenter()->isAjax()) {
			$this->redirect('this');
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


	/********************* Paging *********************/


	/**
	 * @return GridPaginator
	 */
	public function getVisualPaginator()
	{
		return $this->getComponent('paginator');
	}


	/**
	 * @return Paginator
	 */
	public function getPaginator()
	{
		return $this->getVisualPaginator()->getPaginator();
	}



	/**
	 * @return int
	 */
	public function getItemsPerPage()
	{
		return $this->getVisualPaginator()->itemsPerPage;
	}


	/********************* Rendering *********************/


	/**
	 * @param string|array|callable $class
	 * @return Grid
	 */
	public function setRowHtmlClass($class)
	{
		if (is_array($class)) {
			$class = function (Nette\Iterators\CachingIterator $iterator, $record) use ($class) {
				if ($iterator->counter === 0) {
					return NULL;
				}

				$index = count($class) - ($this->counter % count($class));
				return $class[$index];
			};
		}

		if (!is_string($class) && !is_callable($class)) {
			throw new Nette\InvalidArgumentException("Given class must be either string, array or callback, " . gettype($caption) . " given.");
		}

	    $this->rowHtmlClass = $class;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getRowHtmlClass()
	{
		if (!$this->lastIterator) {
			throw new Nette\InvalidStateException("Grind is not beeing rendered.");
		}

		if (is_callable($this->rowHtmlClass)) {
			return call_user_func($this->rowHtmlClass, $this->lastIterator, $this->lastIterator->current());
		}

		return $this->rowHtmlClass;
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
	 * @param Renderers\Template $renderer
	 * @return Grid
	 */
	public function setRenderer(IGridRenderer $renderer)
	{
		$this->renderer = $renderer;
		return $this;
	}



	/**
	 * @return Renderers\Template
	 */
	public function getRenderer()
	{
		if ($this->renderer === FALSE) {
			$this->renderer = new Renderers\Template($this, $this->getTemplate());
		}

		return $this->renderer;
	}



	/**
	 * @return Nette\Templating\FileTemplate
	 */
	protected function createTemplate($class = null)
	{
		return parent::createTemplate($class)
			->setFile(__DIR__ . "/Renderers/templates/table.latte");
	}



	/**
	 * Renders grid
	 */
	public function render()
	{
		$this->getRenderer()->render();
	}

}