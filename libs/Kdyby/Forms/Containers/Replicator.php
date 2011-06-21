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
 * @author Filip Procházka
 * @author Jan Tvrdík
 */
class Replicator extends Container
{

	/** @var callback */
	private $factoryCallback;

	/** @var int */
	private $createDefault;

	/** @var boolean */
	private $submittedBy = FALSE;



	/**
	 * @param callable $factory
	 * @param int $createDefault
	 */
	public function __construct($factory, $createDefault = 0)
	{
		parent::__construct();

		$this->monitor('Nette\Application\UI\Presenter');
		$this->factoryCallback = callback($factory);
		$this->createDefault = (int)$createDefault;
	}



	/**
	 * Magická továrna na komponenty
	 *
	 * @param Nette\ComponentModel\IContainer
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Nette\Application\UI\Presenter) {
			return;
		}

		$this->loadHttpData();
		if (!$this->getForm()->isSubmitted() && $this->createDefault > 0) {
			foreach (range(0, $this->createDefault-1) as $key) {
				$this->createComponent($key);
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
	 * Magická továrna na komponenty
	 *
	 * @param string $name
	 * @return Container
	 */
	protected function createComponent($name)
	{
		$controls = iterator_to_array($this->getComponents(FALSE, 'Nette\Forms\IControl'));
		$firstControl = reset($controls);
		$firstControlName = $firstControl ? $firstControl->name : NULL;

		$container = new Container;
		$container->currentGroup = $this->currentGroup;
		$this->addComponent($container, $name, $firstControlName);

		$this->factoryCallback->invoke($container);

		return $container;
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
				return TRUE;
			}
		}

		return FALSE;
	}



	/**
	 * Vytvoří nový container
	 *
	 * @return Container
	 */
	public function createOne()
	{
		$buttons = array_map(function ($control) {
				return $control->getName();
			}, iterator_to_array($this->getButtons()));

		$containers = iterator_to_array($this->getContainers());
		$lastContainer = end($containers);

		if ($lastContainer) {
			return $this[$lastContainer->name + 1];
		}

		return $this[0];
	}



	/**
	 * @param Container $container
	 * @param boolean $cleanUpGroups
	 * @throws Nette\InvalidArgumentException
	 */
	public function remove(Container $container, $cleanUpGroups = FALSE)
	{
		if (!$container->parent === $this) {
			throw new Nette\InvalidArgumentException('Given component ' . $container->name . ' is not children of ' . $this->name . '.');
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
	 * @param array $subcomponents
	 * @return int
	 */
	public function countFilledWithout(array $components = array(), array $subcomponents = array())
	{
		$httpData = array_diff_key((array)$this->getHttpData(), array_flip($components));

		if (!$httpData) {
			return 0;
		}

		$rows = array();
		$subcomponents = array_flip($subcomponents);
		foreach ($httpData as $item) {
			$rows[] = array_filter(array_diff_key($item, $subcomponents)) ?: FALSE;
		}

		return count(array_filter($rows));
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
	 * @return Nette\Http\Request
	 */
	private function getHttpRequest()
	{
		return $this->getForm()->getPresenter()->getContext()->getService('httpRequest');
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