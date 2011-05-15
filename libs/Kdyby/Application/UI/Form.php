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

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);

		// Allways - your every-day protection
		$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");
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

}


// extension methods
Nette\Forms\Container::extensionMethod('addCheckboxList', array('Kdyby\Forms\Controls\CheckboxList', 'addCheckboxList'));
