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



/**
 * @author Filip Procházka
 * @author Jan Tvrdík
 */
class Replicator extends Nette\Forms\Container
{

	/** @var callback */
	private $factoryCallback;



	/**
	 * @param callable $factory
	 */
	public function __construct($factory)
	{
		parent::__construct();

		$this->monitor('Nette\Application\UI\Presenter');
		$this->factoryCallback = callback($factory);
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
	}



	/**
	 * Magická továrna na komponenty
	 *
	 * @param    string
	 * @return   object
	 */
	protected function createComponent($name)
	{
		$component = $this->addContainer($name);
		$this->factoryCallback->invoke($component);

		return $component;
	}



	/**
	 * @param string $name
	 * @return Nette\Forms\Container
	 */
	public function touch($name)
	{
		return $this->createComponent($name);
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
		/// return $this->getForm()->getPresenter()->getContext()->getService('Nette\Http\IRequest');
		return Nette\Environment::getHttpRequest();
	}



	/**
	 * @param string $methodName
	 */
	public static function register($methodName = 'addDynamic')
	{
		Nette\Forms\Container::extensionMethod($methodName, function ($_this, $name, $factory, $createDefault = 0) {
			$_this[$name] = $replicator = new Replicator($factory);

			if (is_numeric($createDefault) && $createDefault > 0) {
				foreach (range(0, $createDefault-1) as $key) {
					$replicator->touch($key);
				}
			}

			return $replicator;
		});
	}

}