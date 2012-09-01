<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Application\UI;

use Kdyby;
use Nette;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\ISubmitterControl;
use Nette\Utils\Html;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property callable $validateThatControlsAreRendered
 * @method \Kdyby\Forms\Controls\CheckboxList addCheckboxList(string $name, string $label = NULL, array $items = NULL)
 * @method \Kdyby\Forms\Controls\DateTimeInput addDate(string $name, string $label = NULL)
 * @method \Kdyby\Forms\Controls\DateTimeInput addTime(string $name, string $label = NULL)
 * @method \Kdyby\Forms\Controls\DateTimeInput addDatetime(string $name, string $label = NULL)
 * @method \Kdyby\Forms\Containers\Replicator addDynamic(string $name, callback $factory, int $default)
 */
class Form extends Nette\Application\UI\Form
{

	/**
	 * When flag is TRUE, iterates over form controls and if some are rendered and some are not, triggers notice.
	 * @var bool
	 */
	public $checkRendered = TRUE;



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

			$app = $this->getPresenter()->getApplication();
			$app->onShutdown[] = $this->validateThatControlsAreRendered;
		}

		parent::attached($obj);
	}



	/**
	 * @internal
	 */
	public function validateThatControlsAreRendered()
	{
		if (Nette\Diagnostics\Debugger::$productionMode || $this->checkRendered !== TRUE) {
			return;
		}

		$notRendered = $rendered = array();
		foreach ($this->getControls() as $control) {
			/** @var Nette\Forms\Controls\BaseControl $control */
			if (!$control instanceof Nette\Forms\Controls\BaseControl) {
				continue;
			}
			if ($control->getOption('rendered', FALSE)) {
				$rendered[] = $control;

			} else {
				$notRendered[] = $control;
			}
		}

		if ($rendered && $notRendered) {
			$names = array_map(function (BaseControl $control) {
				return get_class($control) . '(' . $control->lookupPath('Nette\Forms\Form') . ')';
			}, $notRendered);

			trigger_error(
				"Some form controls of " . $this->getUniqueId() .
					" were not rendered: " . implode(', ', $names),
				E_USER_NOTICE
			);
		}
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
	 */
	protected function dispatchEvent($listeners, $arg = NULL)
	{
		$args = func_get_args();
		$listeners = array_shift($args);

		foreach ((array)$listeners as $handler) {
			if ($handler instanceof Nette\Application\UI\Link) {
				/** @var \Nette\Application\UI\Link $handler */
				$refl = $handler->getReflection();
				/** @var \Nette\Reflection\ClassType $refl */
				$compRefl = $refl->getProperty('component');
				$compRefl->accessible = TRUE;
				/** @var \Nette\Application\UI\PresenterComponent $component */
				$component = $compRefl->getValue($handler);
				$component->redirect($handler->getDestination(), $handler->getParameters());

			} else {
				callback($handler)->invokeArgs($args);
			}
		}
	}

}

// extension methods
Kdyby\Forms\Controls\CheckboxList::register();
Kdyby\Forms\Controls\DateTimeInput::register();
Kdyby\Extension\Forms\Replicator\Replicator::register();

// radio list helper
RadioList::extensionMethod('getItemsOuterLabel', function (RadioList $_this) {
	$items = array();
	foreach ($_this->items as $key => $value) {
		$html = $_this->getControl($key);
		$html[1]->addClass('radio');

		$items[$key] = $html[1] // label
			->add($html[0]); // control
	}

	return $items;
});

// radio list helper
RadioList::extensionMethod('getFirstItemLabel', function (RadioList $_this) {
	$items = $_this->items;
	$first = key($items);

	$html = $_this->getControl($first);
	$html[1]->addClass('control-label');
	$html[1]->setText($_this->caption);

	return $html[1];
});
