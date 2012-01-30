<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Nette;
use Nette\Forms\ISubmitterControl;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method Kdyby\Forms\Controls\CheckboxList addCheckboxList() addCheckboxList(string $name, string $label, array $items = NULL)
 */
class Form extends Nette\Application\UI\Form
{

	/**
	 */
	public function __construct()
	{
		parent::__construct();

		// overriding constructor is ugly...
		$this->configure();
	}



	/**
	 * Method gets called on construction
	 */
	protected function configure()
	{

	}



	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof Nette\Application\IPresenter) {
			$this->attachHandlers();
		}

		parent::attached($obj);
	}




	/**
	 * Returns a fully-qualified name that uniquely identifies the component
	 * within the presenter hierarchy.
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->lookupPath('Nette\Application\UI\Presenter', TRUE);
	}



	/**
	 * Automatically attach methods
	 */
	protected function attachHandlers()
	{
		if (method_exists($this, 'handleSuccess')) {
			$this->onSuccess[] = callback($this, 'handleSuccess');
		}

		if (method_exists($this, 'handleError')) {
			$this->onError[] = callback($this, 'handleError');
		}

		if (method_exists($this, 'handleValidate')) {
			$this->onValidate[] = callback($this, 'handleValidate');
		}

		foreach ($this->getComponents(TRUE, 'Nette\Forms\ISubmitterControl') as $submitControl) {
			$name = ucfirst((Nette\Utils\Strings::replace(
				$submitControl->lookupPath('Nette\Forms\Form'), '~\-(.)~i', function ($m) {
					return strtoupper($m[1]);
				}
			)));

			if (method_exists($this, 'handle' . $name . 'Click')) {
				$submitControl->onClick[] = callback($this, 'handle' . $name . 'Click');
			}

			if (method_exists($this, 'handle' . $name . 'InvalidClick')) {
				$submitControl->onInvalidClick[] = callback($this, 'handle' . $name . 'InvalidClick');
			}
		}
	}



	/**
	 * Fires send/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;

		} elseif ($this->isSubmitted() instanceof ISubmitterControl) {
			if (!$this->isSubmitted()->getValidationScope() || $this->isValid()) {
				$this->dispatchEvent($this->isSubmitted()->onClick, $this->isSubmitted());
				$valid = TRUE;

			} else {
				$this->dispatchEvent($this->isSubmitted()->onInvalidClick, $this->isSubmitted());
			}
		}

		if (isset($valid) || $this->isValid()) {
			$this->dispatchEvent($this->onSuccess, $this);

		} else {
			$this->dispatchEvent($this->onError, $this);
		}
	}



	/**
	 * @param array|\Traversable $listeners
	 * @param mixed $arg
	 * @param mixed $arg2
	 * @param mixed $arg3
	 */
	protected function dispatchEvent($listeners, $arg = NULL)
	{
		$args = func_get_args();
		$listeners = array_shift($args);

		foreach ((array)$listeners as $handler) {
			if ($handler instanceof Nette\Application\UI\Link) {
				$this->getPresenter()->redirectUrl($handler);

			} else {
				callback($handler)->invokeArgs($args);
			}
		}
	}

}


// extension methods
Nette\Forms\Container::extensionMethod('addCheckboxList', function (Nette\Forms\Container $container, $name, $label, array $items = NULL) {
	return $container[$name] = new Kdyby\Forms\Controls\CheckboxList($label, $items);
});

Nette\Forms\Container::extensionMethod('addDate', function (Nette\Forms\Container $container, $name, $label) {
	return $container[$name] = new Kdyby\Forms\Controls\DateTime($label);
});

Nette\Forms\Container::extensionMethod('addDatetime', function (Nette\Forms\Container $container, $name, $label) {
	return $container[$name] = new Kdyby\Forms\Controls\DateTime($label);
});

Kdyby\Forms\Containers\Replicator::register();
