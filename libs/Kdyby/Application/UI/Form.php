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



/**
 * @author Filip Procházka
 *
 * @method Kdyby\Forms\Controls\CheckboxList addCheckboxList() addCheckboxList(string $name, string $label, array $items = NULL)
 */
class Form extends Nette\Application\UI\Form
{

	public function __construct()
	{
		parent::__construct();
		$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");

		// overriding constructor is ugly...
		$this->configure();
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
	 * Method get's called on construction
	 */
	protected function configure()
	{

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
