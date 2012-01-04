<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Containers;

use Nette;
use Nette\Forms\Container;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @author Jan Tvrdík
 */
class Replicator extends Container
{

	/** @var bool */
	public $forceDefault;

	/** @var int */
	public $createDefault;

	/** @var callback */
	protected $factoryCallback;

	/** @var boolean */
	private $submittedBy = FALSE;

	/** @var array */
	private $created = array();

	/** @var \Nette\Http\IRequest */
	private $httpRequest;



	/**
	 * @param callable $factory
	 * @param int $createDefault
	 * @param bool $forceDefault
	 */
	public function __construct($factory, $createDefault = 0, $forceDefault = FALSE)
	{
		parent::__construct();

		$this->monitor('Nette\Application\UI\Presenter');
		$this->factoryCallback = callback($factory);
		$this->createDefault = (int)$createDefault;
		$this->forceDefault = $forceDefault;
	}



	/**
	 * Magical component factory
	 *
	 * @param \Nette\ComponentModel\IContainer
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Nette\Application\UI\Presenter) {
			return;
		}

		$this->loadHttpData();
		if ($this->createDefault > 0) {
			if (!$this->getForm()->isSubmitted()) {
				foreach (range(0, $this->createDefault - 1) as $key) {
					$this->createComponent($key);
				}

			} elseif ($this->forceDefault) {
				while ($this->getContainers()->count() < $this->createDefault) {
					$this->createOne();
				}
			}
		}
	}



	/**
	 * @param boolean $recursive
	 * @return \ArrayIterator
	 */
	public function getContainers($recursive = FALSE)
	{
		return $this->getComponents($recursive, 'Nette\Forms\Container');
	}



	/**
	 * @param boolean $recursive
	 * @return \ArrayIterator
	 */
	public function getButtons($recursive = FALSE)
	{
		return $this->getComponents($recursive, 'Nette\Forms\ISubmitterControl');
	}



	/**
	 * Magical component factory
	 *
	 * @param string $name
	 * @return \Nette\Forms\Container
	 */
	protected function createComponent($name)
	{
		$container = $this->createContainer();
		$container->currentGroup = $this->currentGroup;
		$this->addComponent($container, $name, $this->getFirstControlName());

		$this->factoryCallback->invoke($container);

		return $this->created[$container->name] = $container;
	}



	/**
	 * @return string
	 */
	private function getFirstControlName()
	{
		$controls = iterator_to_array($this->getComponents(FALSE, 'Nette\Forms\IControl'));
		$firstControl = reset($controls);
		return $firstControl ? $firstControl->name : NULL;
	}



	/**
	 * @return \Nette\Forms\Container
	 */
	protected function createContainer()
	{
		return new Container;
	}



	/**
	 * @return boolean
	 */
	public function isSubmittedBy()
	{
		if ($this->submittedBy) {
			return TRUE;
		}

		foreach ($this->getButtons(TRUE) as $button) {
			if ($button->isSubmittedBy()) {
				return $this->submittedBy = TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Create new container
	 *
	 * @param string|int $name
	 *
	 * @return \Nette\Forms\Container
	 */
	public function createOne($name = NULL)
	{
		if ($name === NULL) {
			$names = array_keys($this->getContainers()->getArrayCopy());
			$name = $names ? max($names) + 1 : 0;
		}

		// Container is overriden, therefore every request for getComponent($name, FALSE) would return container
		if (isset($this->created[$name])) {
			throw new Kdyby\InvalidArgumentException("Container with name '$name' already exists.");
		}

		return $this[$name];
	}



	/**
	 * @param \Nette\Forms\Container $container
	 * @param boolean $cleanUpGroups
	 *
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function remove(Container $container, $cleanUpGroups = FALSE)
	{
		if (!$container->parent === $this) {
			throw new Kdyby\InvalidArgumentException('Given component ' . $container->name . ' is not children of ' . $this->name . '.');
		}

		// to check if form was submitted by this one
		foreach ($container->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $button) {
			if ($button->isSubmittedBy()) {
				$this->submittedBy = TRUE;
				break;
			}
		}

		// get components
		$components = $container->getComponents(TRUE);
		$this->removeComponent($container);

		// reflection is required to hack form groups
		$groupRefl = Nette\Reflection\ClassType::from('Nette\Forms\ControlGroup');
		$controlsProperty = $groupRefl->getProperty('controls');
		$controlsProperty->setAccessible(TRUE);

		// walk groups and clean then from removed components
		$affected = array();
		foreach ($this->getForm()->getGroups() as $group) {
			$groupControls = $controlsProperty->getValue($group);

			foreach ($components as $control) {
				if ($groupControls->contains($control)) {
					$groupControls->detach($control);

					if (!in_array($group, $affected, TRUE)) {
						$affected[] = $group;
					}
				}
			}
		}

		// remove affected & empty groups
		if ($cleanUpGroups && $affected) {
			foreach ($this->getForm()->getComponents(FALSE, 'Nette\Forms\Container') as $container) {
				if ($index = array_search($container->currentGroup, $affected, TRUE)) {
					unset($affected[$index]);
				}
			}

			foreach ($affected as $group) {
				if (!$group->getControls() && in_array($group, $this->getForm()->getGroups(), TRUE)) {
					$this->getForm()->removeGroup($group);
				}
			}
		}
	}



	/**
	 * Loads data received from POST
	 */
	protected function loadHttpData()
	{
		if ($this->getHttpRequest()->isPost()) {
			$values = (array)$this->getHttpData();
			foreach ($values as $key => $value) {
				if (is_array($value) && !$this->getComponent($key, FALSE)) {
					$this->createComponent($key);
				}
			}

			$this->setValues($values);
		}
	}



	/**
	 * Counts filled values, filtered by given names
	 *
	 * @param array $components
	 * @param array $subComponents
	 * @return int
	 */
	public function countFilledWithout(array $components = array(), array $subComponents = array())
	{
		$httpData = array_diff_key((array)$this->getHttpData(), array_flip($components));

		if (!$httpData) {
			return 0;
		}

		$rows = array();
		$subComponents = array_flip($subComponents);
		foreach ($httpData as $item) {
			$rows[] = array_filter(array_diff_key($item, $subComponents)) ?: FALSE;
		}

		return count(array_filter($rows));
	}



	/**
	 * @param array $exceptChildren
	 * @return bool
	 */
	public function isAllFilled(array $exceptChildren = array())
	{
		$components = array();
		foreach ($this->getComponents(FALSE, 'Nette\Forms\IControl') as $control) {
			$components[] = $control->getName();
		}

		foreach ($this->getContainers() as $container) {
			foreach ($container->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $button) {
				$exceptChildren[] = $button->getName();
			}
		}

		$filled = $this->countFilledWithout($components, array_unique($exceptChildren));
		return $filled === count($this->getContainers());
	}



	/**
	 * @return mixed|NULL
	 */
	private function getHttpData()
	{
		$httpRequest = $this->getHttpRequest();

		if ($httpRequest->isPost()) {
			$post = (array)$httpRequest->getPost();

			$chain = array();
			$parent = $this;

			while (!$parent instanceof Nette\Forms\Form) {
				$chain[] = $parent->getName();
				$parent = $parent->getParent();
			};

			while ($chain) {
				$post = &$post[array_pop($chain)];
			}

			return $post;
		}

		return NULL;
	}



	/**
	 * @param \Nette\Http\IRequest $request
	 *
	 * @return \Kdyby\Forms\Containers\Replicator
	 */
	public function setHttpRequest(Nette\Http\IRequest $request)
	{
		$this->httpRequest = $request;
		return $this;
	}



	/**
	 * @return \Nette\Http\Request
	 */
	private function getHttpRequest()
	{
		if ($this->httpRequest !== NULL) {
			return $this->httpRequest;
		}

		return $this->httpRequest = $this->getForm()->getPresenter()
			->getContext()->getByClass('Nette\Http\IRequest');
	}



	/**
	 * @param string $methodName
	 */
	public static function register($methodName = 'addDynamic')
	{
		Container::extensionMethod($methodName, function ($_this, $name, $factory, $createDefault = 0) {
			return $_this[$name] = new Replicator($factory, $createDefault);
		});
	}

}
