<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Filters;

use Kdyby;
use Kdyby\Components\Grinder;
use Nette\Application\UI\Form as UIForm;
use Nette;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Controls\SubmitButton;



/**
 * @author Filip Procházka
 */
class Form extends UIForm
{

	/** @var FiltersMap */
	private $filtersMap;



	/**
	 * @param FiltersMap $filtersMap
	 */
	public function __construct(FiltersMap $filtersMap)
	{
		parent::__construct(NULL, NULL);

		$this->addContainer('filters');
		$this->filtersMap = $filtersMap;

		$this->onSuccess[] = callback($this, 'ProcessFilters');

		// Allways - your every-day protection
		$this->addProtection("Ouchie! Please try to submit the form again, the delivery boy forgot something!");
	}



	/**
	 * @param boolean $need
	 * @return Grid
	 */
	public function getGrid($need = TRUE)
	{
		return $this->lookup('Kdyby\\Components\\Grinder\\Grid', $need);
	}



	/**
	 * @param UIForm $form
	 */
	public function ProcessFilters(UIForm $form)
	{
		$filters = array();

		$values = $form->values['filters'];
		foreach ($this->filtersMap as $filter) {
			if (!array_key_exists($filter->name, $values)) {
				continue;
			}

			$filters[$filter->name] = $values[$filter->name];
		}

		$filters = array_filter($filters, function($value) {
			return !($value === NULL || (is_string($value) && trim($value) === ''));
		});

		$this->getGrid()->redirect('this', array('filter' => $filters));
	}



	/**
	 * @param SubmitButton $button
	 */
	public function ResetFilters(SubmitButton $button)
	{
		$button->getForm()->getGrid()->redirect('this', array('filter' => NULL));
	}

}